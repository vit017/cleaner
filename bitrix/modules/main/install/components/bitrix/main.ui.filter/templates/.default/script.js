;(function() {
	'use strict';

	BX.namespace('BX.Main');


	/**
	 * General filter class
	 * @param {object} arParams Component params
	 * @param {object} options Extends BX.Filter.Settings
	 * @param {object} types Field types from Bitrix\Main\UI\Filter\Type
	 * @param {object} dateTypes Date field types from Bitrix\Main\UI\Filter\DateType
	 * @param {object} numberTypes Number field types from Bitrix\Main\UI\Filter\NumberType
	 */
	BX.Main.Filter = function(arParams, options, types, dateTypes, numberTypes)
	{
		this.params = null;
		this.search = null;
		this.popup = null;
		this.presets = null;
		this.fields = null;
		this.types = null;
		this.dateTypes = null;
		this.settings = null;
		this.filter = null;
		this.api = null;
		this.isAddPresetModeState = false;
		this.init(arParams, options, types, dateTypes, numberTypes);
	};

	//noinspection JSUnusedGlobalSymbols
	BX.Main.Filter.prototype = {
		init: function(arParams, options, types, dateTypes, numberTypes)
		{
			try {
				this.params = arParams;
				/**
				 * @type {Object}
				 * @property STRING
				 * @property SELECT
				 * @property DATE
				 * @property MULTI_SELECT
				 * @property NUMBER
				 * @property CUSTOM_ENTITY
				 * @property CHECKBOX
				 * @property CUSTOM
				 * @property ENTITY
				 */
				this.types = types;

				/**
				 * @type {object}
				 * @property NONE
				 * @property YESTERDAY
				 * @property CURRENT_DAY
				 * @property CURRENT_WEEK
				 * @property CURRENT_MONTH
				 * @property CURRENT_QUARTER
				 * @property LAST_7_DAYS
				 * @property LAST_30_DAYS
				 * @property LAST_60_DAYS
				 * @property LAST_90_DAYS
				 * @property MONTH
				 * @property QUARTER
				 * @property YEAR
				 * @property EXACT
				 * @property LAST_WEEK
				 * @property LAST_MONTH
				 * @property RANGE
				 * @property NEXT_DAYS
				 * @property PREV_DAYS
				 */
				this.dateTypes = dateTypes;
				this.numberTypes = numberTypes;
				this.settings = new BX.Filter.Settings(options, this);

				BX.bind(document, 'mousedown', BX.delegate(this._onDocumentClick, this));

				if (this.getParam('GRID_ID'))
				{
					BX.addCustomEvent('Grid::ready', BX.delegate(this._onGridReady, this));
				}
			} catch (err) {
				throw err;
			}

			BX.bind(document, 'keydown', BX.delegate(this._onDocumentKeydown, this));

			this.getSearch().updatePreset(this.getParam('CURRENT_PRESET'));

			if (this.getParam('CLEAR_GET'))
			{
				this.clearGet();
			}

			BX.bind(window, 'load', BX.delegate(function() {
				this.settings.get('AUTOFOCUS') && this.adjustFocus();
			}, this));

			this.firstInit = true;
		},

		clearGet: function()
		{
			if ('history' in window)
			{
				var url = window.location.toString();
				var clearUrl = BX.util.remove_url_param(url, 'apply_filter');
				window.history.replaceState(null, '', clearUrl);
			}
		},

		adjustFocus: function()
		{
			this.getSearch().adjustFocus();
		},

		_onAddPresetKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'enter'))
			{
				this._onSaveButtonClick();
			}
		},

		_onDocumentKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'escape'))
			{
				if (this.getPopup().isShown())
				{
					BX.onCustomEvent(window, 'BX.Main.Filter:blur', [this]);
					this.closePopup();

					if (this.getParam('VALUE_REQUIRED_MODE'))
					{
						this.restoreRemovedPreset();
					}
				}
			}
		},

		getApi: function()
		{
			if (!(this.api instanceof BX.Filter.Api))
			{
				this.api = new BX.Filter.Api(this);
			}

			return this.api;
		},

		addSidebarItem: function(id, name, pinned)
		{
			var Presets = this.getPreset();
			var presetsContainer = Presets.getContainer();
			var sidebarItem = Presets.createSidebarItem(id, name, pinned);
			var preset = Presets.getPresetNodeById(id);

			if (BX.type.isDomNode(preset))
			{
				BX.remove(preset);
				BX.prepend(sidebarItem, presetsContainer);
			}
			else
			{
				presetsContainer && presetsContainer.insertBefore(sidebarItem, Presets.getAddPresetField());
			}

			BX.bind(sidebarItem, 'click', BX.delegate(Presets._onPresetClick, Presets));
		},

		saveUserSettings: function(forAll)
		{
			var optionsParams = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER_ARRAY'};
			var Presets = this.getPreset();
			var currentPresetId = Presets.getCurrentPresetId();
			var presetsSettings = {};

			this.params['PRESETS'] = BX.clone(this.editablePresets);
			presetsSettings.current_preset = currentPresetId;

			Presets.getPresets().forEach(function(current, index) {
				var presetId = Presets.getPresetId(current);

				if (presetId && presetId !== 'tmp_filter')
				{
					var presetData = Presets.getPreset(presetId);
					var presetRows = presetData.FIELDS.map(function(curr) { return curr.NAME; });

					presetData.TITLE = BX.util.htmlspecialchars(BX.util.htmlspecialcharsback(presetData.TITLE));
					presetData.SORT = index;
					Presets.updatePresetName(current, presetData.TITLE);

					presetsSettings[presetId] = {
						sort: index,
						name: presetData.TITLE,
						fields: this.preparePresetSettingsFields(presetData.FIELDS, presetRows)
					}
				}
			}, this);

			this.saveOptions(presetsSettings, optionsParams, null, forAll);
		},

		isForAll: function()
		{
			var checkbox = this.getForAllCheckbox();
			return (checkbox && checkbox.checked);
		},

		getForAllCheckbox: function()
		{
			if (!this.forAllCheckbox)
			{
				this.forAllCheckbox = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classForAllCheckbox);
			}

			return this.forAllCheckbox;
		},

		preparePresetSettingsFields: function(fields)
		{
			var result = {};
			var valuesKeys;

			(fields || []).forEach(function(current) {
				switch (current.TYPE)
				{
					case this.types.STRING : {
						result[current.NAME] = current.VALUE;
						break;
					}

					case this.types.SELECT : {
						result[current.NAME] = 'VALUE' in current.VALUE ? current.VALUE.VALUE : '';
						break;
					}

					case this.types.MULTI_SELECT : {
						if (BX.type.isArray(current.VALUE) && current.VALUE.length)
						{
							current.VALUE.forEach(function(curr, index) {
								result[current.NAME] = BX.type.isPlainObject(result[current.NAME]) ? result[current.NAME] : {};
								result[current.NAME][index] = curr.VALUE;
							}, this);
						}
						break;
					}

					case this.types.CHECKBOX : {
						if (BX.type.isArray(current.VALUE) && current.VALUE.length)
						{
							current.VALUE.forEach(function(curr, index) {
								result[current.NAME] = BX.type.isPlainObject(result[current.NAME]) ? result[current.NAME] : {};
								result[current.NAME][index] = curr.VALUE;
							}, this);
						}
						break;
					}

					case this.types.DATE : {
						if (BX.type.isPlainObject(current.VALUES))
						{
							valuesKeys = Object.keys(current.VALUES);
							result[current.NAME + '_datesel'] = current.SUB_TYPE.VALUE;
							valuesKeys.forEach(function(curr) {
								result[current.NAME + curr] = current.VALUES[curr];
							}, this);
						}
						break;
					}

					case this.types.NUMBER : {
						if (BX.type.isPlainObject(current.VALUES))
						{
							valuesKeys = Object.keys(current.VALUES);
							result[current.NAME + '_numsel'] = current.SUB_TYPE.VALUE;
							valuesKeys.forEach(function(curr) {
								result[current.NAME + curr] = current.VALUES[curr];
							}, this);
						}
						break;
					}

					case this.types.CUSTOM_ENTITY : {
						if (BX.type.isPlainObject(current.VALUES))
						{
							result[current.NAME] = current.VALUES._value;
							result[current.NAME + '_label'] = current.VALUES._label;
						}
						break;
					}

					default : {
						break;
					}
				}
			}, this);

			return result;
		},

		savePreset: function()
		{
			var presetId = 'filter_' + (+new Date());
			var presetName = BX.util.htmlspecialcharsback(this.getPreset().getAddPresetFieldInput().value);

			this.updatePreset(presetId, presetName, null, true);
			this.addSidebarItem(presetId, presetName);
			this.getPreset().applyPreset(presetId);
			this.getPreset().activatePreset(presetId);
			this.applyFilter();
		},

		updatePreset: function(presetId, presetName, reset, sort, beforeLoad, afterLoad)
		{
			var fields = this.getFilterFieldsValues();
			var sourceFields = this.getPreset().getFields().map(function(curr) { return BX.data(curr, 'name'); });
			var preset = this.getPreset().getCurrentPresetData();
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER'};
			var rows, value, tmpPresetNode, tmpPresetInput, presets;
			var data = {};

			rows = Object.keys(fields);

			if (!reset)
			{
				data.apply_filter = 'Y';
			}
			else
			{
				data.clear_filter = 'Y';
			}

			data.save = 'Y';
			data.fields = fields;
			data.rows = sourceFields.join(',');

			data.preset_id = presetId || preset.ID;

			if (BX.type.isNotEmptyString(presetName))
			{
				data.name = BX.util.htmlspecialchars(presetName);
			}
			else
			{
				tmpPresetNode = this.getPreset().getPresetNodeById(data.preset_id);
				tmpPresetInput = this.getPreset().getPresetInput(tmpPresetNode);

				if (BX.type.isDomNode(tmpPresetInput) && BX.type.isNotEmptyString(tmpPresetInput.value))
				{
					data.name = tmpPresetInput.value;
				}
				else
				{
					data.name = preset.TITLE;
				}
			}

			if ((!('sort' in data) || !BX.type.isNumber(data.sort)) && sort)
			{
				presets = this.getParam('PRESETS');
				data.sort = presets.length + 2;
			}

			if (!reset)
			{
				rows.forEach(function(key) {
					if (BX.type.isArray(data.fields[key]))
					{
						value = data.fields[key].length ? {} : '';

						data.fields[key].forEach(function(val, index) {
							value[index] = val;
						}, this);

						if (value || BX.type.isNumber(value) || BX.type.isBoolean(value))
						{
							data.fields[key] = value;
						}
					}
				}, this);
			}

			if (data.preset_id === 'tmp_filter' || this.isAddPresetEnabled() || reset)
			{
				this.updateParams(data);
			}

			if (BX.type.isFunction(beforeLoad))
			{
				beforeLoad();
			}

			var promise = new BX.Promise(null, this);
			promise.setAutoResolve('fulfill', 0);

			promise.then(function() {
				var afterPromise = new BX.Promise(null, this);
				this.saveOptions(data, params, BX.proxy(afterPromise.fulfill, afterPromise));
				return afterPromise;
			})
			.then(function() {
				!!afterLoad && afterLoad();
			});

			return promise;
		},

		saveFieldsSort: function()
		{
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER'};
			var fields = this.getPreset().getFields();
			var data = {};

			data.preset_id = 'default_filter';

			if (BX.type.isArray(fields))
			{
				data.rows = fields.map(function(current) {
					return BX.data(current, 'name');
				});
				data.rows = data.rows.join(',');
			}

			this.updateParams(data);
			this.saveOptions(data, params);
		},

		updateParams: function(data)
		{
			var preset, presets;
			var fields = [];

			if (BX.type.isPlainObject(data) && 'preset_id' in data)
			{
				preset = this.getPreset().getPreset(data.preset_id);

				if (BX.type.isPlainObject(preset))
				{
					if ('name' in data && BX.type.isNotEmptyString(data.name))
					{
						preset.TITLE = data.name;
					}

					if ('rows' in data && !('fields' in data))
					{
						data.fields = {};

						data.rows.split(',').forEach(function(curr) {
							data.fields[curr] = '';
						});
					}

					if ('fields' in data)
					{
						preset.FIELDS = this.preparePresetFields(data.fields, data.rows);
					}
				}
				else
				{
					presets = this.getParam('PRESETS');
					preset = {
						ID: data.preset_id,
						TITLE: data.name,
						SORT: (presets.length + 2),
						FIELDS: this.preparePresetFields(data.fields, data.rows)
					};

					presets.push(preset);
				}
			}
		},

		preparePresetFields: function(dataFields, rows)
		{
			var fieldKeys, field;
			var fields = [];

			if (BX.type.isPlainObject(dataFields))
			{
				rows = BX.type.isNotEmptyString(rows) ? rows.split(',') : [];
				fieldKeys = rows.length ? rows : Object.keys(dataFields);
				fieldKeys.forEach(function(current) {
					current = current.replace('_datesel', '').replace('_numsel', '');
					field = BX.clone(this.getFieldByName(current));

					if (BX.type.isPlainObject(field))
					{
						if (field.TYPE === this.types.STRING)
						{
							field.VALUE = dataFields[current];
						}

						if (field.TYPE === this.types.MULTI_SELECT)
						{
							field.VALUE = this.prepareMultiSelectValue(dataFields[current], field.ITEMS);
						}

						if (field.TYPE === this.types.SELECT || field.TYPE === this.types.CHECKBOX)
						{
							field.VALUE = this.prepareSelectValue(dataFields[current], field.ITEMS);
						}

						if (field.TYPE === this.types.DATE)
						{
							field.SUB_TYPE = this.prepareSelectValue(dataFields[current + '_datesel'], field.SUB_TYPES);

							field.VALUES = {
								'_from': dataFields[current + '_from'],
								'_to': dataFields[current + '_to'],
								'_days': dataFields[current + '_days'],
								'_month': dataFields[current + '_days'],
								'_quarter': dataFields[current + '_quarter'],
								'_year': dataFields[current + '_year']
							};
						}

						if (field.TYPE === this.types.NUMBER)
						{
							field.SUB_TYPE = this.prepareSelectValue(dataFields[current + '_numsel'], field.SUB_TYPES);
							field.VALUES = {
								'_from': dataFields[current + '_from'],
								'_to': dataFields[current + '_to']
							};
						}

						if (field.TYPE === this.types.CUSTOM_ENTITY)
						{
							if (typeof dataFields[current + '_label'] !== 'undefined')
							{
								field.VALUES._label = dataFields[current + '_label'];
							}

							if (typeof dataFields[current] !== 'undefined')
							{
								field.VALUES._value = dataFields[current];
							}
						}

						if (field.TYPE === this.types.CUSTOM)
						{
							field._VALUE = dataFields[current];
						}

						fields.push(field);
					}
				}, this);
			}

			return fields;
		},

		prepareSelectValue: function(value, items)
		{
			var result = {};
			var tmpResult;

			if (BX.type.isNotEmptyString(value) && BX.type.isArray(items))
			{
				tmpResult = this.prepareMultiSelectValue({0: value}, items);
				result = tmpResult.length > 0 ? tmpResult[0] : {};
			}
			else
			{
				result = items[0];
			}

			return result;
		},

		prepareMultiSelectValue: function(values, items)
		{
			var result = [];

			if (BX.type.isPlainObject(values) && BX.type.isArray(items))
			{
				var valuesKeys = Object.keys(values);
				var valuesValues = valuesKeys.map(function(curr) { return values[curr]; });

				result = items.filter(function(current) {
					return valuesValues.some(function(val) { return val === current.VALUE});
				}, this);
			}

			return result;
		},

		getFieldByName: function(name)
		{
			var fields = this.getParam('FIELDS');

			fields = fields.filter(function(current) {
				return current.NAME === name;
			}, this);

			return fields.length > 0 ? fields[0] : null;
		},

		saveOptions: function(postData, getData, callback, forAll)
		{
			if (this.isUseCommonPresets())
			{
				if (BX.type.isPlainObject(getData))
				{
					getData.common_presets_id = this.getParam('COMMON_PRESETS_ID');
				}
			}

			var url = BX.util.add_url_param(this.getParam('SETTINGS_URL'), getData || {});

			forAll = BX.type.isBoolean(forAll) ? forAll : !!this.getSaveForAllCheckbox() && this.getSaveForAllCheckbox().checked;

			if (forAll && ('action' in getData && getData.action === 'SET_FILTER_ARRAY'))
			{
				var action = {
					CONFIRM: true,
					CONFIRM_MESSAGE: this.getParam('MAIN_UI_FILTER__CONFIRM_MESSAGE_FOR_ALL'),
					CONFIRM_APPLY_BUTTON: this.getParam('MAIN_UI_FILTER__CONFIRM_APPLY_FOR_ALL'),
					CONFIRM_CANCEL_BUTTON: this.getParam('CONFIRM_CANCEL')
				};

				this.confirmDialog(
					action,
					BX.delegate(function() {
						url = BX.util.add_url_param(url, {'for_all': 'true'});
						this.disableEdit();
						this.disableAddPreset();
						save(url, this, true);
					}, this),
					BX.delegate(function() {

					}, this)
				);
			}
			else
			{
				save(url, this);
			}

			function save(url, ctx)
			{
				BX.ajax.post(url, postData, BX.delegate(function() {
					BX.removeClass(this.getFindButton(), this.settings.classWaitButtonClass);
					BX.type.isFunction(callback) && callback();
				}, ctx));
			}
		},

		prepareEvent: function(event)
		{
			var i, x;

			if (!('path' in event) || !event.path.length)
			{
				event.path = [event.target];
				i = 0;

				while ((x = event.path[i++].parentNode) !== null)
				{
					event.path.push(x);
				}
			}

			return event;
		},

		restoreRemovedPreset: function()
		{
			if (this.getParam('VALUE_REQUIRED_MODE'))
			{
				var currentPreset = this.getParam('CURRENT_PRESET');
				if (BX.type.isPlainObject(currentPreset))
				{
					var currentPresetId = currentPreset.ID;
					var presetNode = this.getPreset().getPresetNodeById(currentPresetId);
					this.getPreset().applyPreset(currentPresetId);
					this.getPreset().activatePreset(presetNode);
				}
			}
		},

		hasScrollClick: function(event)
		{
			var x = 'clientX' in event ? event.clientX : 'x' in event ? event.x : 0;
			return x >= document.documentElement.offsetWidth;
		},

		isUseCommonPresets: function()
		{
			return !!this.getParam('COMMON_PRESETS_ID');
		},

		/**
		 * Checks whether event is inside filter
		 * @param {MouseEvent} event
		 * @returns {boolean}
		 */
		isInsideFilterEvent: function(event)
		{
			event = this.prepareEvent(event);
			return (event.path || []).some(function(current) {
				return (
					BX.type.isDomNode(current) && (
						BX.hasClass(current, this.settings.classFilterContainer) ||
						BX.hasClass(current, this.settings.classSearchContainer) ||
						BX.hasClass(current, this.settings.classDefaultPopup) ||
						BX.hasClass(current, this.settings.classPopupOverlay)
					)
				);
			}, this);
		},

		_onDocumentClick: function(event)
		{
			var popup = this.getPopup();

			if (!this.isInsideFilterEvent(event) && !this.hasScrollClick(event))
			{
				if (popup && popup.isShown())
				{
					this.closePopup();

					if (this.getParam('VALUE_REQUIRED_MODE'))
					{
						this.restoreRemovedPreset();
					}
				}

				BX.onCustomEvent(window, 'BX.Main.Filter:blur', [this]);
			}
		},

		_onAddFieldClick: function(event)
		{
			var popup = this.getFieldsPopup();
			event.stopPropagation();
			event.preventDefault();

			if (popup && !popup.isShown())
			{
				this.showFieldsPopup();
				this.syncFields();
			}
			else
			{
				this.closeFieldListPopup();
			}
		},

		syncFields: function()
		{
			var fields = this.getPreset().getFields();
			var items = this.getFieldsPopupItems();
			var currentId, isNeedCheck;

			if (BX.type.isArray(items) && items.length)
			{
				items.forEach(function(current) {
					currentId = BX.data(current, 'name').replace('_datesel', '').replace('_numsel', '');
					isNeedCheck = fields.some(function(field) {
						return BX.data(field, 'name') === currentId;
					});
					if (isNeedCheck)
					{
						BX.addClass(current, this.settings.classMenuItemChecked);
					}
					else
					{
						BX.removeClass(current, this.settings.classMenuItemChecked);
					}
				}, this);
			}
		},

		getFieldsPopupItems: function()
		{
			var popup;

			if (!BX.type.isArray(this.fieldsPopupItems))
			{
				popup = this.getFieldsPopup();

				if ('contentContainer' in popup && BX.type.isDomNode(popup.contentContainer))
				{
					this.fieldsPopupItems = BX.Filter.Utils.getByClass(popup.contentContainer, this.settings.classMenuItem, true);
				}
			}

			return this.fieldsPopupItems;
		},

		getFieldListContainerClassName: function(itemsCount)
		{
			var containerClass = this.settings.classPopupFieldList1Column;

			if (itemsCount > 6 && itemsCount < 12)
			{
				containerClass = this.settings.classPopupFieldList2Column;
			}

			if (itemsCount > 12)
			{
				containerClass = this.settings.classPopupFieldList3Column;
			}

			return containerClass;
		},

		prepareFieldsDecl: function(fields)
		{
			return (fields || []).map(function(item) {
				return {
					block: 'main-ui-filter-field-list-item',
					label: 'LABEL' in item ? item.LABEL : '',
					id: 'ID' in item ? item.ID : '',
					name: 'NAME' in item ? item.NAME : '',
					item: item,
					onClick: BX.delegate(this._clickOnFieldListItem, this)
				};
			}, this);
		},

		/**
		 * Gets fields popup content
		 * @returns {HTMLElement} - Fields container with fields
		 */
		getFieldsListPopupContent: function()
		{
			var fields = this.getParam('FIELDS');
			var fieldsCount = BX.type.isArray(fields) ? fields.length : 0;
			var containerDecl = {
				block: this.settings.classPopupFieldList,
				mix: this.getFieldListContainerClassName(fieldsCount),
				content: this.prepareFieldsDecl(fields)
			};

			return BX.decl(containerDecl);
		},

		_clickOnFieldListItem: function(event)
		{
			var target = event.target;
			var data;

			if (!BX.hasClass(target, this.settings.classFieldListItem))
			{
				target = BX.findParent(target, {class: this.settings.classFieldListItem}, true, false);
			}

			if (BX.type.isDomNode(target))
			{
				try {
					data = JSON.parse(BX.data(target, 'item'));
				} catch (err) {}

				if (BX.hasClass(target, this.settings.classMenuItemChecked))
				{
					BX.removeClass(target, this.settings.classMenuItemChecked);
					this.getPreset().removeField(data);
				}
				else
				{
					if (BX.type.isPlainObject(data))
					{
						this.getPreset().addField(data);
						BX.addClass(target, this.settings.classMenuItemChecked);
					}
				}

				this.syncFields();
			}
		},

		showFieldsPopup: function()
		{
			var popup = this.getFieldsPopup();
			this.adjustFieldListPopupPosition();
			popup.show();
		},

		closeFieldListPopup: function()
		{
			var popup = this.getFieldsPopup();
			popup.close();
		},

		adjustFieldListPopupPosition: function()
		{
			var popup = this.getFieldsPopup();
			var pos = BX.pos(this.getAddField());
			pos.forceBindPosition = true;
			popup.adjustPosition(pos);
		},

		getFieldsPopup: function()
		{
			var addFiledButton = this.getAddField();

			if (!this.fieldsPopup)
			{
				this.fieldsPopup = new BX.PopupWindow(
					this.getParam('FILTER_ID') + '_fields_popup',
					addFiledButton,
					{
						autoHide : true,
						offsetTop : 4,
						offsetLeft : 0,
						lightShadow : true,
						closeIcon : false,
						closeByEsc : false,
						noAllPaddings: true,
						zIndex: 13
					}
				);

				this.fieldsPopup.setContent(this.getFieldsListPopupContent());
			}

			return this.fieldsPopup;
		},

		_onAddPresetClick: function()
		{
			this.enableAddPreset();
		},

		enableWaitSate: function(button)
		{
			button && BX.addClass(button, this.settings.classWaitButtonClass);
		},

		disableWaitState: function(button)
		{
			button && BX.removeClass(button, this.settings.classWaitButtonClass);
		},

		_onSaveButtonClick: function()
		{
			var forAll = !!this.getSaveForAllCheckbox() && this.getSaveForAllCheckbox().checked;

			this.enableWaitSate(this.getFindButton());

			if (this.isAddPresetEnabled() && !forAll)
			{
				this.savePreset();
				this.disableAddPreset();
			}

			if (this.isEditEnabled())
			{
				var preset = this.getPreset();
				preset.updateEditablePreset(preset.getCurrentPresetId());
				this.saveUserSettings(forAll);
				!forAll && this.disableEdit();
			}
		},

		_onCancelButtonClick: function()
		{
			this.disableAddPreset();
			this.getPreset().clearAddPresetFieldInput();
			this.disableEdit();
			!!this.getSaveForAllCheckbox() && (this.getSaveForAllCheckbox().checked = null);
		},

		_onGridReady: function(grid)
		{
			if (!this.grid && grid.getContainerId() === this.getParam('GRID_ID'))
			{
				this.grid = grid;
			}
		},

		_onFilterMousedown: function(event)
		{
			var target = event.target;

			if (this.getFields().isDragButton(target))
			{
				var inputs = BX.Filter.Utils.getByTag(target.parentNode, 'input', true);

				(inputs || []).forEach(function(item) {
					BX.fireEvent(item, 'blur');
				});

				BX.fireEvent(this.getFilter(), 'click');
			}
		},

		_onFilterClick: function(event)
		{
			var Fields = this.getFields();
			var Presets = this.getPreset();
			var field;

			if (Fields.isFieldDelete(event.target))
			{
				field = Fields.getField(event.target);
				Presets.removeField(field);
			}

			if (Fields.isFieldValueDelete(event.target))
			{
				field = Fields.getField(event.target);
				Fields.clearFieldValue(field);
			}
		},

		getButtonsContainer: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classButtonsContainer);
		},

		getSaveButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classSaveButton);
		},

		getCancelButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classCancelButton);
		},

		getFindButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classFindButton);
		},

		getResetButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classResetButton);
		},

		getAddPresetButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classAddPresetButton);
		},

		isAddPresetEnabled: function()
		{
			return this.isAddPresetModeState;
		},

		enableAddPreset: function()
		{
			var Preset = this.getPreset();
			var addPresetField = Preset.getAddPresetField();
			var addPresetFieldInput = Preset.getAddPresetFieldInput();
			var buttonsContainer = this.getButtonsContainer();

			BX.show(addPresetField);
			BX.show(buttonsContainer);
			BX.hide(this.getPresetButtonsContainer());
			this.hideForAllCheckbox();

			if (BX.type.isDomNode(addPresetFieldInput))
			{
				addPresetFieldInput.focus();
			}

			BX.addClass(this.getSidebarControlsContainer(), this.settings.classDisabled);

			this.isAddPresetModeState = true;
		},

		disableAddPreset: function()
		{
			var Preset = this.getPreset();
			var addPresetField = Preset.getAddPresetField();
			var buttonsContainer = this.getButtonsContainer();
			var presetId = Preset.getCurrentPresetId();

			BX.hide(addPresetField);
			BX.hide(buttonsContainer);
			BX.show(this.getPresetButtonsContainer());
			this.showForAllCheckbox();

			if (BX.type.isNotEmptyString(presetId))
			{
				Preset.applyPreset(presetId);
			}

			Preset.getAddPresetFieldInput().value = '';

			BX.removeClass(this.getSidebarControlsContainer(), this.settings.classDisabled);

			this.isAddPresetModeState = false;
		},

		getControls: function()
		{
			var container = this.getFieldListContainer();
			var controls = null;

			if (BX.type.isDomNode(container))
			{
				controls = BX.Filter.Utils.getByClass(container, this.settings.classControl, true);
			}

			return controls;
		},

		getFilterFields: function()
		{
			var container = this.getFieldListContainer();
			var fields = [];
			var groups = [];

			if (BX.type.isDomNode(container))
			{
				fields = BX.Filter.Utils.getByClass(container, this.settings.classField, true);
				groups = BX.Filter.Utils.getByClass(container, this.settings.classFieldGroup, true);

				if (!BX.type.isArray(fields))
				{
					fields = [];
				}

				if (BX.type.isArray(groups))
				{
					groups.forEach(function(current) {
						fields.push(current);
					});
				}
			}

			return fields;
		},

		getFilterFieldsValues: function()
		{
			var fields = this.getPreset().getFields();
			var Search = this.getSearch();
			var values = {};
			var type, name;

			values['FIND'] = Search.getInput().value;

			if (BX.type.isArray(fields) && fields.length)
			{
				fields.forEach(function(current) {
					type = BX.data(current, 'type');
					name = BX.data(current, 'name');

					switch (type) {
						case this.types.STRING : {
							this.prepareControlStringValue(values, current);
							break;
						}

						case this.types.NUMBER : {
							this.prepareControlNumberValue(values, name, current);
							break;
						}

						case this.types.DATE : {
							this.prepareControlDateValue(values, name, current);
							break;
						}

						case this.types.SELECT : {
							this.prepareControlSelectValue(values, name, current);
							break;
						}

						case this.types.MULTI_SELECT : {
							this.prepareControlMultiselectValue(values, name, current);
							break;
						}

						case this.types.CUSTOM : {
							this.prepareControlCustomValue(values, name, current);
							break;
						}

						case this.types.CUSTOM_ENTITY : {
							this.prepareControlCustomEntityValue(values, name, current);
							break;
						}

						default : {
							break;
						}
					}
				}, this);
			}

			return values;
		},

		prepareControlCustomEntityValue: function(values, name, field)
		{
			var squares = this.fetchSquares(field);
			var squaresData = this.fetchSquaresData(squares);
			var isMultiple = BX.Main.ui.CustomEntity.isMultiple(field);

			values[name] = '';
			values[name + '_label'] = '';

			if (isMultiple)
			{
				values[name] = [];
				values[name + '_label'] = [];

				!!squaresData && squaresData.forEach(function(item) {
					values[name].push(item._value.toString());
					values[name + '_label'].push(item._label.toString());
				});
			}
			else
			{
				if (squaresData.length)
				{
					values[name] = squaresData[0]._value.toString();
					values[name + '_label'] = squaresData[0]._label.toString();
				}
			}
		},


		/**
		 * @param {HTMLElement} field
		 * @return {HTMLElement[]}
		 */
		fetchSquares: function(field)
		{
			return !!field ? BX.Filter.Utils.getByClass(field, this.settings.classSquare, true) : [];
		},


		/**
		 * @param {HTMLElement[]} squares
		 * @return {object[]}
		 */
		fetchSquaresData: function(squares)
		{
			return squares.map(function(square) {
				return JSON.parse(BX.data(square, 'item'));
			}, this);
		},


		/**
		 * @param {object} values
		 * @param {string} name
		 * @param {HTMLElement} field
		 */
		prepareControlCustomValue: function(values, name, field)
		{
			var stringFields = BX.Filter.Utils.getByTag(field, 'input', true);

			values[name] = '';

			if (BX.type.isArray(stringFields))
			{
				stringFields.forEach(function(current) {
					if (BX.type.isNotEmptyString(current.name))
					{
						values[current.name] = current.value;
					}
				});
			}
		},

		prepareControlMultiselectValue: function(values, name, field)
		{
			var select = BX.Filter.Utils.getByClass(field, this.settings.classMultiSelect);
			var value = JSON.parse(BX.data(select, 'value'));

			values[name] = '';

			if (BX.type.isArray(value) && value.length)
			{
				values[name] = {};
				value.forEach(function(current, index) {
					values[name][index] = current.VALUE;
				});
			}
		},

		prepareControlSelectValue: function(values, name, field)
		{
			var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
			var value = JSON.parse(BX.data(select, 'value'));

			values[name] = value.VALUE;
		},

		prepareControlDateValue: function(values, name, field)
		{
			var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
			var selectName = name + this.settings.datePostfix;
			var fromName = name + this.settings.fromPostfix;
			var toName = name + this.settings.toPostfix;
			var daysName = name + this.settings.daysPostfix;
			var monthName = name + this.settings.monthPostfix;
			var quarterName = name + this.settings.quarterPostfix;
			var yearName = name + this.settings.yearPostfix;
			var selectValue, stringFields, controls, controlName;

			values[selectName] = '';
			values[fromName] = '';
			values[toName] = '';
			values[daysName] = '';
			values[monthName] = '';
			values[quarterName] = '';
			values[yearName] = '';

			selectValue = JSON.parse(BX.data(select, 'value'));

			values[selectName] = selectValue.VALUE;

			switch (selectValue.VALUE) {
				case this.dateTypes.EXACT : {
					stringFields = BX.Filter.Utils.getByClass(field, this.settings.classDateInput);
					values[fromName] = stringFields.value;
					values[toName] = stringFields.value;
					break;
				}

				case this.dateTypes.QUARTER : {
					controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

					if (BX.type.isArray(controls))
					{
						controls.forEach(function(current) {
							controlName = BX.data(current, 'name');

							if (controlName && controlName.indexOf('_quarter') !== -1)
							{
								values[quarterName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}

							if (controlName && controlName.indexOf('_year') !== -1)
							{
								values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}
						}, this);
					}
					break;
				}

				case this.dateTypes.YEAR : {
					controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

					if (BX.type.isArray(controls))
					{
						controls.forEach(function(current) {
							controlName = BX.data(current, 'name');

							if (controlName && controlName.indexOf('_year') !== -1)
							{
								values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}
						}, this);
					}
					break;
				}

				case this.dateTypes.MONTH : {
					controls = BX.Filter.Utils.getByClass(field, this.settings.classControl, true);

					if (BX.type.isArray(controls))
					{
						controls.forEach(function(current) {
							controlName = BX.data(current, 'name');

							if (controlName && controlName.indexOf('_month') !== -1)
							{
								values[monthName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}

							if (controlName && controlName.indexOf('_year') !== -1)
							{
								values[yearName] = JSON.parse(BX.data(current, 'value')).VALUE;
							}
						}, this);
					}
					break;
				}

				case this.dateTypes.NEXT_DAYS :
				case this.dateTypes.PREV_DAYS : {
					var control = BX.Filter.Utils.getByClass(field, this.settings.classNumberInput);

					if (!!control && control.name === daysName)
					{
						values[daysName] = control.value;
					}

					break;
				}

				case this.dateTypes.RANGE : {
					stringFields = BX.Filter.Utils.getByClass(field, this.settings.classDateInput, true);
					stringFields.forEach(function(current) {
						if (current.name === fromName)
						{
							values[fromName] = current.value;
						}
						else if (current.name === toName)
						{
							values[toName] = current.value;
						}
					}, this);
					break;
				}

				default : {
					break;
				}
			}
		},

		prepareControlNumberValue: function(values, name, field)
		{
			var stringFields = BX.Filter.Utils.getByClass(field, this.settings.classNumberInput, true);
			var select = BX.Filter.Utils.getByClass(field, this.settings.classSelect);
			var selectName = name + this.settings.numberPostfix;
			var fromName = name + this.settings.fromPostfix;
			var toName = name + this.settings.toPostfix;
			var selectValue;

			values[fromName] = '';
			values[toName] = '';

			selectValue = JSON.parse(BX.data(select, 'value'));
			values[selectName] = selectValue.VALUE;

			stringFields.forEach(function(current) {
				if (current.name.indexOf(this.settings.fromPostfix) !== -1)
				{
					values[fromName] = current.value || '';

					if (values[selectName] === 'exact')
					{
						values[toName] = current.value || '';
					}
				}
				else if (current.name.indexOf(this.settings.toPostfix) !== -1)
				{
					values[toName] = current.value || '';
				}
			}, this);
		},

		prepareControlStringValue: function(values, field)
		{
			var control = BX.Filter.Utils.getByClass(field, this.settings.classStringInput);
			var name;

			if (BX.type.isDomNode(control))
			{
				name = control.name;
				values[name] = control.value;
			}
		},


		showGridAnimation: function()
		{
			this.grid && this.grid.tableFade();
		},

		hideGridAnimation: function()
		{
			this.grid && this.grid.tableUnfade();
		},

		/**
		 * @private
		 * @param {?Boolean} clear - is need reset filter
		 * @param {?Boolean} applyPreset - is need apply preset
		 * @return {String}
		 */
		getPresetId: function(clear, applyPreset)
		{
			var presetId = this.getPreset().getCurrentPresetId();

			if ((!this.isEditEnabled() && !this.isAddPresetEnabled() && !applyPreset) ||
				(presetId === 'default_filter' && !clear))
			{
				presetId = 'tmp_filter';
			}

			return presetId;
		},

		/**
		 * Applies filter
		 * @param {?Boolean} [clear] - is need reset filter
		 * @param {?Boolean} [applyPreset] - is need apply preset
		 * @return {BX.Promise}
		 */
		applyFilter: function(clear, applyPreset)
		{
			var presetId = this.getPresetId(clear, applyPreset);
			var filterId = this.getParam('FILTER_ID');
			var promise = new BX.Promise(null, this);
			var Preset = this.getPreset();
			var Search = this.getSearch();
			var applyParams = {autoResolve: !this.grid};
			var self = this;

			this.showGridAnimation();
			BX.onCustomEvent(window, 'BX.Main.Filter:beforeApply', [filterId, {}, this, promise]);

			this.updatePreset(presetId, null, clear, null).then(function() {
				Search.updatePreset(Preset.getPreset(presetId));
			}).then(function() {
				var params = {apply_filter: 'Y', clear_nav: 'Y'};
				var fulfill = BX.delegate(promise.fulfill, promise);
				var reject = BX.delegate(promise.reject, promise);
				self.grid && self.grid.reloadTable('POST', params, fulfill, reject);
				BX.onCustomEvent(window, 'BX.Main.Filter:apply', [filterId, {}, self, promise, applyParams]);
				applyParams.autoResolve && promise.fulfill();
			});

			return promise;
		},

		getAddField: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classAddField);
		},

		getFieldListContainer: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classFileldControlList);
		},

		getFields: function()
		{
			if (!(this.fields instanceof BX.Filter.Fields))
			{
				this.fields = new BX.Filter.Fields(this);
			}

			return this.fields;
		},

		getPreset: function()
		{
			if (!(this.presets instanceof BX.Filter.Presets))
			{
				this.presets = new BX.Filter.Presets(this);
			}

			return this.presets;
		},

		resetControlData: function(controlData)
		{
			if (BX.type.isPlainObject(controlData))
			{
				switch (controlData.TYPE)
				{
					case this.types.MULTI_SELECT : {
						controlData.VALUE = [];
						break;
					}

					case this.types.SELECT : {
						controlData.VALUE = controlData.ITEMS[0];
						break;
					}

					case this.types.DATE : {
						controlData.SUB_TYPE = controlData.SUB_TYPES[0];
						controlData.VALUES = {
							'_from': '',
							'_to': '',
							'_days': '',
							'_quarter': '',
							'_year': ''
						};
						break;
					}

					case this.types.NUMBER : {
						controlData.SUB_TYPE = controlData.SUB_TYPES[0];
						controlData.VALUES = {
							'_from': '',
							'_to': ''
						};
						break;
					}

					case this.types.CUSTOM_ENTITY : {
						controlData.VALUES = {
							'_label': '',
							'_value': ''
						};
						break;
					}

					case this.types.CUSTOM : {
						controlData._VALUE = '';
						break;
					}

					default : {
						controlData.VALUE = '';
					}
				}
			}

			return controlData;
		},

		clearControl: function(name)
		{
			var control = this.getPreset().getField({NAME: name});
			var controlData, newControl;

			if (BX.type.isDomNode(control))
			{
				controlData = this.getFieldByName(name);
				controlData = this.resetControlData(controlData);

				newControl = this.getPreset().createControl(controlData);
				BX.insertAfter(newControl, control);
				BX.remove(control);
			}
		},

		clearControls: function(squareData)
		{
			if (BX.type.isArray(squareData))
			{
				squareData.forEach(function(item) {
					'name' in item && this.clearControl(item.name);
				}, this);
			}

			else if (BX.type.isPlainObject(squareData) && 'name' in squareData)
			{
				this.clearControl(squareData.name);
			}
		},

		getTemplate: function()
		{
			return BX.html(BX(this.settings.generalTemplateId));
		},

		isIe: function()
		{
			if (!BX.type.isBoolean(this.ie))
			{
				this.ie = BX.hasClass(document.documentElement, 'bx-ie');
			}

			return this.ie;
		},

		closePopup: function()
		{
			var popup = this.getPopup();
			var popupContainer = popup.popupContainer;
			var configCloseDelay = this.settings.get('FILTER_CLOSE_DELAY');
			var closeDelay;

			setTimeout(BX.delegate(function() {

				if (!this.isIe())
				{
					BX.removeClass(popupContainer, this.settings.classAnimationShow);
					BX.addClass(popupContainer, this.settings.classAnimationClose);

					closeDelay = parseFloat(BX.style(popupContainer, 'animation-duration'));

					if (BX.type.isNumber(closeDelay))
					{
						closeDelay = closeDelay * 1000;
					}

					setTimeout(function() {
						popup.close();
					}, closeDelay);
				}
				else
				{
					popup.close();
				}
			}, this), configCloseDelay);

			this.closeFieldListPopup();
			this.adjustFocus();
		},

		showPopup: function()
		{
			var popup = this.getPopup();
			var popupContainer;

			if (!popup.isShown())
			{
				this.isOpened = true;
				var showDelay = this.settings.get('FILTER_SHOW_DELAY');

				setTimeout(BX.delegate(function() {
					popup.show();

					if (!this.isIe())
					{
						popupContainer = popup.popupContainer;
						BX.removeClass(popupContainer, this.settings.classAnimationClose);
						BX.addClass(popupContainer, this.settings.classAnimationShow);
					}
				}, this), showDelay);
			}
		},

		getSaveForAllCheckbox: function()
		{
			if (!this.saveForAllCheckbox && !!this.getSaveForAllCheckboxContainer())
			{
				this.saveForAllCheckbox = BX.Filter.Utils.getBySelector(this.getSaveForAllCheckboxContainer(), 'input[type="checkbox"]');
			}

			return this.saveForAllCheckbox;
		},

		getSaveForAllCheckboxContainer: function()
		{
			if (!this.saveForAllCheckboxContainer)
			{
				this.saveForAllCheckboxContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classForAllCheckbox);
			}

			return this.saveForAllCheckboxContainer;
		},

		showForAllCheckbox: function()
		{
			!!this.getSaveForAllCheckboxContainer() &&
				BX.removeClass(this.getSaveForAllCheckboxContainer(), this.settings.classHide);
		},

		hideForAllCheckbox: function()
		{
			!!this.getSaveForAllCheckboxContainer() &&
				BX.addClass(this.getSaveForAllCheckboxContainer(), this.settings.classHide);
		},

		getPopupBindElement: function()
		{
			if (!this.popupBindElement)
			{
				var selector = this.settings.get('POPUP_BIND_ELEMENT_SELECTOR');
				var result = null;

				if (BX.type.isNotEmptyString(selector))
				{
					result = BX.Filter.Utils.getBySelector(document, selector);
				}

				this.popupBindElement = !!result ? result : this.getSearch().getContainer();
			}

			return this.popupBindElement;
		},

		getPopup: function()
		{
			if (!(this.popup instanceof BX.PopupWindow))
			{
				this.popup =  new BX.PopupWindow(
					this.getParam('FILTER_ID') + this.settings.searchContainerPostfix,
					this.getPopupBindElement(),
					{
						autoHide : false,
						offsetTop : parseInt(this.settings.get('POPUP_OFFSET_TOP')),
						offsetLeft : parseInt(this.settings.get('POPUP_OFFSET_LEFT')),
						lightShadow : true,
						closeIcon : false,
						closeByEsc : false,
						noAllPaddings: true,
						zIndex: 12
					}
				);

				this.popup.setContent(this.getTemplate());
				BX.bind(this.getFieldListContainer(), 'keydown', BX.delegate(this._onFieldsContainerKeydown, this));
				BX.bind(this.getFilter(), 'click', BX.delegate(this._onFilterClick, this));
				BX.bind(this.getAddPresetButton(), 'click', BX.delegate(this._onAddPresetClick, this));
				BX.bind(this.getPreset().getAddPresetFieldInput(), 'keydown', BX.delegate(this._onAddPresetKeydown, this));
				BX.bind(this.getPreset().getContainer(), 'keydown', BX.delegate(this._onPresetInputKeydown, this));
				BX.bind(this.getSaveButton(), 'click', BX.delegate(this._onSaveButtonClick, this));
				BX.bind(this.getCancelButton(), 'click', BX.delegate(this._onCancelButtonClick, this));
				BX.bind(this.getFindButton(), 'click', BX.delegate(this._onFindButtonClick, this));
				BX.bind(this.getResetButton(), 'click', BX.delegate(this._onResetButtonClick, this));
				BX.bind(this.getAddField(), 'click', BX.delegate(this._onAddFieldClick, this));
				BX.bind(this.getEditButton(), 'click', BX.delegate(this._onEditButtonClick, this));
				BX.bind(this.getRestoreButton(), 'click', BX.delegate(this._onRestoreButtonClick, this));
				BX.bind(this.getRestoreFieldsButton(), 'click', BX.delegate(this._onRestoreFieldsButtonClick, this));
				this.getFilter().addEventListener('mousedown', BX.delegate(this._onFilterMousedown, this), true);
				this.getPreset().showCurrentPresetFields();
				this.getPreset().bindOnPresetClick();
			}

			return this.popup;
		},

		_onRestoreFieldsButtonClick: function()
		{
			this.restoreDefaultFields();
		},

		restoreDefaultFields: function()
		{
			var defaultPreset = this.getPreset().getPreset('default_filter', true);
			var presets = this.getParam('PRESETS');
			var currentPresetId = this.getPreset().getCurrentPresetId();
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'SET_FILTER'};
			var fields = defaultPreset.FIELDS.map(function(curr) { return curr.NAME; });
			var rows = fields.join(',');

			presets.forEach(function(current, index) {
				if (current.ID === 'default_filter')
				{
					presets[index] = BX.clone(defaultPreset);
				}
			}, this);

			if (BX.type.isArray(this.editablePresets))
			{
				this.editablePresets.forEach(function(current, index) {
					if (current.ID === 'default_filter')
					{
						this.editablePresets[index] = BX.clone(defaultPreset);
					}
				}, this);
			}

			this.getPreset().applyPreset(currentPresetId);
			this.updatePreset(currentPresetId);
			this.saveOptions({preset_id: "default_filter", rows: rows, save: "Y", apply_filter: "N"}, params);
		},

		getRestoreFieldsButton: function()
		{
			if (!this.restoreFieldsButton)
			{
				this.restoreFieldsButton = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classRestoreFieldsButton);
			}

			return this.restoreFieldsButton;
		},

		restoreFilter: function()
		{
			var defaultPresets = this.getParam('DEFAULT_PRESETS');
			var allPresets = this.getParam('PRESETS');
			var isReplace = false;
			var replaceIndex, applyPresetId, presetNode;

			if (BX.type.isArray(defaultPresets))
			{
				defaultPresets.sort(function(a, b) {
					return a.SORT < b.SORT;
				});

				defaultPresets.forEach(function(defPreset) {
					isReplace = allPresets.some(function(current, index) {
						if (current.ID === defPreset.ID)
						{
							replaceIndex = index;
							return true;
						}
					});

					if (isReplace)
					{
						allPresets[replaceIndex] = BX.clone(defPreset);
					}
					else
					{
						allPresets.push(BX.clone(defPreset));
					}

					if (defPreset.ID !== 'default_filter')
					{
						this.addSidebarItem(defPreset.ID, defPreset.TITLE, defPreset.PINNED);

						if (defPreset.PINNED)
						{
							applyPresetId = defPreset.ID;
						}
					}
				}, this);
			}

			this.saveRestoreFilter();
			this.disableAddPreset();
			this.disableEdit();

			if (!applyPresetId)
			{
				applyPresetId = "default_filter";
			}

			presetNode = this.getPreset().getPresetNodeById(applyPresetId);

			if (presetNode)
			{
				BX.fireEvent(presetNode, 'click');
			}
		},

		saveRestoreFilter: function()
		{
			var params = {'FILTER_ID': this.getParam('FILTER_ID'), 'GRID_ID': this.getParam('GRID_ID'), 'action': 'RESTORE_FILTER'};
			var presets = this.getParam('PRESETS');
			var data = {};
			var rows;

			if (BX.type.isArray(presets))
			{
				presets.forEach(function(current, index) {
					rows = current.FIELDS.map(function(field) {
						return field.NAME;
					});
					rows = rows.join(',');
					data[current.ID] = {
						name: current.TITLE || null,
						sort: index,
						preset_id: current.ID,
						fields:  this.prepareFields(current.FIELDS),
						rows: rows
					};
				}, this);

				this.saveOptions(data, params);
			}
		},

		prepareFields: function(fields)
		{
			var result = {};
			var valuesKeys;

			if (BX.type.isArray(fields))
			{
				fields.forEach(function(current) {
					if (current.TYPE === this.types.SELECT)
					{
						result[current.NAME] = 'VALUE' in current.VALUE ? current.VALUE.VALUE : '';
					}

					if (current.TYPE === this.types.MULTI_SELECT)
					{
						current.VALUE.forEach(function(val, i) {
							result[current.NAME] = result[current.NAME] || {};
							result[current.NAME][i] = val.VALUE;
						});

						result[current.NAME] = result[current.NAME] || '';
					}

					if (current.TYPE === this.types.DATE ||
						current.TYPE === this.types.NUMBER)
					{
						valuesKeys = Object.keys(current.VALUES);

						valuesKeys.forEach(function(key) {
							result[current.NAME + key] = current.VALUES[key];
						});

						if (current.TYPE === this.types.DATE)
						{
							result[current.NAME + '_datesel'] = 'VALUE' in current.SUB_TYPE ?
								current.SUB_TYPE.VALUE : current.SUB_TYPES[0].VALUE;
						}

						if (current.TYPE === this.types.NUMBER)
						{
							result[current.NAME + '_numsel'] = 'VALUE' in current.SUB_TYPE ?
								current.SUB_TYPE.VALUE : current.SUB_TYPES[0].VALUE;
						}
					}

					if (current.TYPE === this.types.CUSTOM_ENTITY)
					{
						result[current.NAME + '_label'] = current.VALUES._label;
						result[current.NAME + '_value'] = current.VALUES._value;
					}
				}, this);
			}

			return result;
		},

		getRestoreButton: function()
		{
			if (!BX.type.isDomNode(this.restoreButton))
			{
				this.restoreButton = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classRestoreButton);
			}

			return this.restoreButton;
		},

		_onPresetInputKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'enter') && event.target.tagName === 'INPUT')
			{
				BX.fireEvent(this.getSaveButton(), 'click');
			}
		},

		_onFieldsContainerKeydown: function(event)
		{
			if (BX.Filter.Utils.isKey(event, 'enter') && event.target.tagName === 'INPUT')
			{
				this.applyFilter();
				this.closePopup();
			}
		},

		_onFindButtonClick: function()
		{
			//var currentPresetId = this.getPreset().getCurrentPresetId();
			//
			//if (currentPresetId !== 'tmp_filter' && !this.getPreset().isPresetValuesModified(currentPresetId))
			//{
			//	var preset = this.getPreset().getPreset(currentPresetId);
			//	var additional = this.getPreset().getAdditionalValues(currentPresetId);
			//	var rows = this.getPreset().getFields().map(function(current) { return BX.data(current, 'name'); });
			//	preset.ADDITIONAL = this.preparePresetFields(additional, rows);
			//	this.getPreset().applyPreset(currentPresetId);
			//}
			//else
			//{
				this.getPreset().deactivateAllPresets();
				this.applyFilter();
				this.closePopup();
			//}
		},

		_onResetButtonClick: function()
		{
			if (this.getParam('RESET_TO_DEFAULT_MODE'))
			{
				this.getSearch().clearInput();
				this.getPreset().applyPinnedPreset();
			}
			else
			{
				this.resetFilter();
			}

			this.closePopup();
		},

		/**
		 * @param withoutSearch
		 * @return {BX.Promise}
		 */
		resetFilter: function(withoutSearch)
		{
			var Search = this.getSearch();
			var Presets = this.getPreset();

			if (!withoutSearch)
			{
				Search.clearInput();
			}

			Search.removePreset();
			Presets.deactivateAllPresets();
			Presets.resetPreset(true);
			Search.hideClearButton();
			Search.adjustPlaceholder();
			return this.applyFilter(true, true);
		},

		_onEditButtonClick: function()
		{
			if (!this.isEditEnabled())
			{
				this.enableEdit();
			}
			else
			{
				this.disableEdit();
			}
		},

		enableFieldsDragAndDrop: function()
		{
			var fields = this.getPreset().getFields();

			this.fieldsList = [];

			if (BX.type.isArray(fields))
			{
				this.fieldsList = fields.map(this.registerDragItem, this);
			}
		},

		registerDragItem: function(item)
		{
			var dragButton = this.getDragButton(item);

			dragButton.onbxdragstart = BX.delegate(this._onFieldDragStart, this);
			dragButton.onbxdragstop = BX.delegate(this._onFieldDragStop, this);
			dragButton.onbxdrag = BX.delegate(this._onFieldDrag, this);
			jsDD.registerObject(dragButton);
			jsDD.registerDest(dragButton);
			return item;
		},

		unregisterDragItem: function(item)
		{
			var dragButton = this.getDragButton(item);
			jsDD.unregisterObject(dragButton);
			jsDD.unregisterDest(dragButton);
		},

		_onFieldDragStart: function()
		{
			this.dragItem = this.getFields().getField(jsDD.current_node);
			this.dragIndex = BX.Filter.Utils.getIndex(this.fieldsList, this.dragItem);
			this.dragRect = this.dragItem.getBoundingClientRect();
			this.offset = this.dragRect.height;
			this.dragStartOffset = (jsDD.start_y - (this.dragRect.top + BX.scrollTop(window)));

			BX.Filter.Utils.styleForEach(this.fieldsList, {'transition': '100ms'});
			BX.addClass(this.dragItem, this.settings.classPresetOndrag);
			BX.bind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
		},

		_onFieldDragStop: function()
		{
			BX.unbind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
			BX.removeClass(this.dragItem, this.settings.classPresetOndrag);

			BX.Filter.Utils.styleForEach(this.fieldsList, {'transition': '', 'transform': ''});
			BX.Filter.Utils.collectionSort(this.dragItem, this.targetItem);

			this.fieldsList = this.getPreset().getFields();

			this.saveFieldsSort();
		},

		_onFieldDrag: function()
		{
			var self = this;
			var currentRect, currentMiddle;

			this.dragOffset = (this.realY - this.dragRect.top - this.dragStartOffset);
			this.sortOffset = self.realY + BX.scrollTop(window);

			BX.Filter.Utils.styleForEach([this.dragItem], {
				'transition': '0ms',
				'transform': 'translate3d(0px, '+this.dragOffset+'px, 0px)'
			});

			this.fieldsList.forEach(function(current, index) {
				if (current)
				{
					currentRect = current.getBoundingClientRect();
					currentMiddle = currentRect.top + BX.scrollTop(window) + (currentRect.height / 2);

					if (index > self.dragIndex && self.sortOffset > currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(-self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(-self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (index < self.dragIndex && self.sortOffset < currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (((index < self.dragIndex && self.sortOffset > currentMiddle) ||
						(index > self.dragIndex && self.sortOffset < currentMiddle)) &&
						current.style.transform !== 'translate3d(0px, 0px, 0px)')
					{
						if (current.style.transform !== '')
						{
							self.targetItem = current;
						}

						BX.style(current, 'transform', 'translate3d(0px, 0px, 0px)');
						BX.style(current, 'transition', '300ms');
					}
				}
			});
		},

		disableFieldsDragAndDrop: function()
		{
			if (BX.type.isArray(this.fieldsList) && this.fieldsList.length)
			{
				this.fieldsList.map(this.unregisterDragItem, this);
			}
		},

		enablePresetsDragAndDrop: function()
		{
			var Preset, presets, dragButton, presetId;

			Preset = this.getPreset();
			presets = Preset.getPresets();
			this.presetsList = [];

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					presetId = Preset.getPresetId(current);

					if (!BX.hasClass(current, this.settings.classAddPresetField) &&
						presetId !== 'default_filter' &&
						!BX.hasClass(current, this.settings.classDefaultFilter))
					{
						dragButton = this.getDragButton(current);
						dragButton.onbxdragstart = BX.delegate(this._onDragStart, this);
						dragButton.onbxdragstop = BX.delegate(this._onDragStop, this);
						dragButton.onbxdrag = BX.delegate(this._onDrag, this);
						jsDD.registerObject(dragButton);
						jsDD.registerDest(dragButton);
						this.presetsList.push(current);
					}
				}, this);
			}
		},

		getDragButton: function(presetNode)
		{
			return BX.Filter.Utils.getByClass(presetNode, this.settings.classPresetDragButton);
		},

		disablePresetsDragAndDrop: function()
		{
			if (BX.type.isArray(this.presetsList) && this.presetsList.length)
			{
				this.presetsList.forEach(function(current) {
					if (!BX.hasClass(current, this.settings.classAddPresetField))
					{
						jsDD.unregisterObject(current);
						jsDD.unregisterDest(current);
					}
				}, this);
			}
		},

		_onDragStart: function()
		{
			this.dragItem = this.getPreset().normalizePreset(jsDD.current_node);
			this.dragIndex = BX.Filter.Utils.getIndex(this.presetsList, this.dragItem);
			this.dragRect = this.dragItem.getBoundingClientRect();
			this.offset = this.dragRect.height;
			this.dragStartOffset = (jsDD.start_y - (this.dragRect.top + BX.scrollTop(window)));

			BX.Filter.Utils.styleForEach(this.list, {'transition': '100ms'});
			BX.addClass(this.dragItem, this.settings.classPresetOndrag);
			BX.bind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
		},

		_onMouseMove: function(event)
		{
			this.realX = event.clientX;
			this.realY = event.clientY;
		},

		getDragOffset: function()
		{
			return (jsDD.x - this.startDragOffset - this.dragRect.left);
		},

		_onDragStop: function()
		{
			var Preset, presets;

			BX.unbind(document, 'mousemove', BX.delegate(this._onMouseMove, this));
			BX.removeClass(this.dragItem, this.settings.classPresetOndrag);

			BX.Filter.Utils.styleForEach(this.presetsList, {'transition': '', 'transform': ''});
			BX.Filter.Utils.collectionSort(this.dragItem, this.targetItem);

			Preset = this.getPreset();
			presets = Preset.getPresets();
			this.presetsList = [];

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					if (!BX.hasClass(current, this.settings.classAddPresetField) &&
						!BX.hasClass(current, this.settings.classDefaultFilter))
					{
						this.presetsList.push(current);
					}
				}, this);
			}

		},

		_onDrag: function()
		{
			var self = this;
			var currentRect, currentMiddle;

			this.dragOffset = (this.realY - this.dragRect.top - this.dragStartOffset);
			this.sortOffset = self.realY + BX.scrollTop(window);

			BX.Filter.Utils.styleForEach([this.dragItem], {
				'transition': '0ms',
				'transform': 'translate3d(0px, '+this.dragOffset+'px, 0px)'
			});

			this.presetsList.forEach(function(current, index) {
				if (current)
				{
					currentRect = current.getBoundingClientRect();
					currentMiddle = currentRect.top + BX.scrollTop(window) + (currentRect.height / 2);

					if (index > self.dragIndex && self.sortOffset > currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(-self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(-self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (index < self.dragIndex && self.sortOffset < currentMiddle &&
						current.style.transform !== 'translate3d(0px, '+(self.offset)+'px, 0px)' &&
						current.style.transform !== '')
					{
						self.targetItem = current;
						BX.style(current, 'transform', 'translate3d(0px, '+(self.offset)+'px, 0px)');
						BX.style(current, 'transition', '300ms');
					}

					if (((index < self.dragIndex && self.sortOffset > currentMiddle) ||
						(index > self.dragIndex && self.sortOffset < currentMiddle)) &&
						current.style.transform !== 'translate3d(0px, 0px, 0px)')
					{
						if (current.style.transform !== '')
						{
							self.targetItem = current;
						}

						BX.style(current, 'transform', 'translate3d(0px, 0px, 0px)');
						BX.style(current, 'transition', '300ms');
					}
				}
			});
		},


		getSidebarControlsContainer: function()
		{
			if (!BX.type.isDomNode(this.sidebarControlsContainer))
			{
				this.sidebarControlsContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classSidebarControlsContainer);
			}

			return this.sidebarControlsContainer;
		},

		enableEdit: function()
		{
			var Preset = this.getPreset();
			var presets = Preset.getPresets();
			var presetId;

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					presetId = Preset.getPresetId(current);
					if (!BX.hasClass(current, this.settings.classAddPresetField) && presetId !== 'default_filter')
					{
						BX.addClass(current, this.settings.classPresetEdit);
					}
				}, this);
			}

			this.enablePresetsDragAndDrop();
			BX.show(this.getButtonsContainer());
			BX.hide(this.getPresetButtonsContainer());
			BX.addClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
			this.editablePresets = BX.clone(this.getParam('PRESETS'));
			this.isEditEnabledState = true;
		},

		disableEdit: function()
		{
			var Preset = this.getPreset();
			var presets = Preset.getPresets();

			if (BX.type.isArray(presets) && presets.length)
			{
				presets.forEach(function(current) {
					if (!BX.hasClass(current, this.settings.classAddPresetField))
					{
						BX.removeClass(current, this.settings.classPresetEdit);
						this.getPreset().disableEditPresetName(current);
					}
				}, this);
			}

			this.disablePresetsDragAndDrop();

			if (!this.isAddPresetEnabled())
			{
				BX.style(this.getButtonsContainer(), 'display', '');
			}

			BX.show(this.getPresetButtonsContainer());
			BX.removeClass(this.getSidebarControlsContainer(), this.settings.classDisabled);
			this.editablePresets = null;
			this.isEditEnabledState = false;
			this.applyFilter(null, true);
		},

		getPresetButtonsContainer: function()
		{
			if (!BX.type.isDomNode(this.presetButtonsContainer))
			{
				this.presetButtonsContainer = BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classPresetButtonsContainer);
			}

			return this.presetButtonsContainer;
		},

		isEditEnabled: function()
		{
			return this.isEditEnabledState;
		},

		getEditButton: function()
		{
			return BX.Filter.Utils.getByClass(this.getFilter(), this.settings.classEditButton);
		},

		/**
		 * Gets component param by param name
		 * @param {string} paramName
		 * @param {*} [defaultValue] - Be returns if param with paramName not set
		 * @returns {*}
		 */
		getParam: function(paramName, defaultValue)
		{
			return paramName in this.params ? this.params[paramName] : defaultValue;
		},

		/**
		 * Gets container of filter popup
		 * @returns {HTMLElement|null}
		 */
		getFilter: function()
		{
			return BX.Filter.Utils.getByClass(this.getPopup().contentContainer, this.settings.classFilterContainer);
		},

		/**
		 * @returns {BX.Filter.Search}
		 */
		getSearch: function()
		{
			if (!(this.search instanceof BX.Filter.Search))
			{
				this.search = new BX.Filter.Search(this);
			}

			return this.search;
		},

		_onRestoreButtonClick: function()
		{
			var action = {
				CONFIRM: true,
				CONFIRM_MESSAGE: this.getParam('CONFIRM_MESSAGE'),
				CONFIRM_APPLY_BUTTON: this.getParam('CONFIRM_APPLY'),
				CONFIRM_CANCEL_BUTTON: this.getParam('CONFIRM_CANCEL')
			};

			this.confirmDialog(action, BX.delegate(this.restoreFilter, this));
		},


		/**
		 * Shows confirmation popup
		 * @param {object} action - Popup properties
		 * @param {boolean} action.CONFIRM - true If the user must confirm the action
		 * @param {string} action.CONFIRM_MESSAGE - Message of confirm popup
		 * @param {string} action.CONFIRM_APPLY_BUTTON - Text of apply button
		 * @param {string} action.CONFIRM_CANCEL_BUTTON - Text of cancel button
		 * @param {string} [action.CONFIRM_TITLE] - Title of confirm popup
		 * @param {function} then - Callback after a successful confirmation
		 * @param {function} [cancel] - callback after cancel confirmation
		 */
		confirmDialog: function(action, then, cancel)
		{
			if ('CONFIRM' in action && action.CONFIRM)
			{
				var dialogId = this.getParam('FILTER_ID') + '-confirm-dialog';
				var popupMessage = '<div class="main-ui-filter-confirm-content">'+action.CONFIRM_MESSAGE+'</div>';
				var popupTitle = 'CONFIRM_TITLE' in action ? action.CONFIRM_TITLE : '';

				var applyButton = new BX.PopupWindowButton({
					text: action.CONFIRM_APPLY_BUTTON,
					events: {
						click: function()
						{
							BX.type.isFunction(then) ? then() : null;
							this.popupWindow.close();
							this.popupWindow.destroy();
						}
					}
				});

				var cancelButton = new BX.PopupWindowButtonLink({
					text: action.CONFIRM_CANCEL_BUTTON,
					events: {
						click: function()
						{
							BX.type.isFunction(cancel) ? cancel() : null;
							this.popupWindow.close();
							this.popupWindow.destroy();
						}
					}
				});

				var dialog = new BX.PopupWindow(
					dialogId,
					null,
					{
						content: popupMessage,
						titleBar: popupTitle,
						autoHide: false,
						zIndex: 9999,
						overlay: 0.4,
						offsetTop: -100,
						closeIcon : true,
						closeByEsc : true,
						buttons: [applyButton, cancelButton]
					}
				);

				BX.addCustomEvent(dialog, 'onPopupClose', BX.delegate(function() {
					!!this.getSaveForAllCheckbox() && (this.getSaveForAllCheckbox().checked = null);
				}, this));

				if (!dialog.isShown())
				{
					dialog.show();
					var popupContainer = dialog.popupContainer;
					BX.removeClass(popupContainer, this.settings.classAnimationShow);
					BX.addClass(popupContainer, this.settings.classAnimationShow);
				}
			}
			else
			{
				BX.type.isFunction(then) ? then() : null;
			}
		}
	};
})();


(function() {
	BX.Main.filterManager = {
		data: {},

		push: function(id, instance)
		{
			if (BX.type.isNotEmptyString(id) && instance)
			{
				this.data[id] = instance;
			}
		},

		getById: function(id)
		{
			var result = null;

			if (id in this.data)
			{
				result = this.data[id];
			}

			return result;
		}
	};
})();
