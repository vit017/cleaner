;(function() {
	'use strict';

	BX.namespace('BX.Main');

	BX.Main.selectorManager = {
		controls: {}
	};

	/**
	 * General selector class
	 * @param arParams
	 * @param options
	 * @param types
	 * @param dateTypes
	 * @param numberTypes
	 */
	BX.Main.Selector = function()
	{
		this.initialized = false;
		this.blockInit = false;

		this.id = "";
		this.input = null;
		this.options = null;
		this.callback = null;
		this.items = null;
		this.entities = null;
		this.mainPopupWindow = null;
		this.entitiesSet = [
			'users', 'emails', 'crmemails', 'groups', 'sonetgroups', 'department', 'departmentRelation', 'contacts', 'companies', 'leads', 'deals'
		];
	};

	BX.Main.Selector.controls = {};

	BX.Main.Selector.create = function(params)
	{

		if(
			typeof params.id == 'undefined'
			|| !params.id
		)
		{
			params.id = BX.util.hashCode(Math.random().toString());
		}
		else if (typeof BX.Main.selectorManager.controls[params.id] != 'undefined')
		{
			return BX.Main.selectorManager.controls[params.id];
		}

		var self = new BX.Main.Selector();
		self.init(params);
		BX.Main.selectorManager.controls[self.getId()] = self;

		return self;

	};

	BX.Main.Selector.prototype = {
		init: function(params)
		{
			try {
				if(!("SocNetLogDestination" in BX))
				{
					throw new ReferenceError('No BX.SocNetLogDestination detected. Forgot to include socialnetwork module and/or its assets?');
				}

			} catch (err) {
				throw err;
			}

			this.id = params.id;
			this.input = (params.inputId && BX(params.inputId) ? BX(params.inputId) : null);
			this.containerNode = (params.containerId && BX(params.containerId) ? BX(params.containerId) : null);
			this.bindNode = (params.bindId && BX(params.bindId) ? BX(params.bindId) : this.containerNode);
			this.tag = (params.tagId && BX(params.tagId) ? BX(params.tagId) : null);

			this.options = params.options || {};
			this.callback = params.callback || null;

			this.items = params.items || null;
			this.entities = params.entities || null;

			var parameters = {
				name : this.id,
				pathToAjax: (params.pathToAjax ? params.pathToAjax : null),
				searchInput : this.input || null,
				bindMainPopup : {
					node: this.bindNode,
					offsetTop: '5px',
					offsetLeft: '15px'
				},
				bindSearchPopup : {
					node : this.bindNode,
					offsetTop: '5px',
					offsetLeft: '15px'
				},
				useClientDatabase: (this.getOption('useClientDatabase') == 'Y'),
				sendAjaxSearch: (this.getOption('sendAjaxSearch') != 'N'),
				showSearchInput: (this.getOption('useSearch') == 'Y'),
				allowAddUser: (this.getOption('allowAddUser') == 'Y'),
				allowAddCrmContact: (this.getOption('allowAddCrmContact') == 'Y'),
				allowAddSocNetGroup: (this.getOption('allowAddSocNetGroup') == 'Y'),
				allowSearchEmailUsers: (this.getOption('allowSearchEmailUsers') == 'Y'),
				allowSearchCrmEmailUsers: (this.getOption('allowSearchCrmEmailUsers') == 'Y'),
				allowSearchNetworkUsers: (this.getOption('allowSearchNetworkUsers') == 'Y'),
				enableDepartments: (this.getOption('enableDepartments') == 'Y'),
				departmentSelectDisable: (this.getOption('departmentSelectDisable') == 'Y'),
				enableSonetgroups: (this.getOption('enableSonetgroups') == 'Y'),
				isCrmFeed: (this.getOption('isCrmFeed') == 'Y'),
				callback : {
					select : this.callback.select,
					unSelect: this.callback.unSelect,
					openDialog: this.callback.openDialog,
					closeDialog: this.callback.closeDialog,
					openSearch: this.callback.openSearch,
					closeSearch: this.callback.closeSearch,
					openEmailAdd: this.callback.openEmailAdd,
					closeEmailAdd: this.callback.closeEmailAdd
				},
				allowSonetGroupsAjaxSearchFeatures: this.getOption('allowSonetGroupsAjaxSearchFeatures')
			};

			var key = null;
			parameters.items = {};

			for (var i = 0; i < this.entitiesSet.length; i++)
			{
				key = this.entitiesSet[i];
				parameters.items[key] = this.entities[key] || {};
			}

			parameters.itemsLast = {};
			parameters.itemsSelected = this.items.selected || {};

			BX.SocNetLogDestination.init(parameters);

			if (this.input)
			{
				this.initDialog();

				if (this.tag)
				{
					BX.bind(this.tag, "focus", BX.delegate(function(e) {
						this.initDialog({
							realParams: true,
							bByFocusEvent: true
						});
						return BX.PreventDefault(e);
					}, this));

					BX.SocNetLogDestination.BXfpSetLinkName({
						formName: this.id,
						tagInputName: params.tagId,
						tagLink1: BX.message('BX_FPD_LINK_1'),
						tagLink2: BX.message('BX_FPD_LINK_2')
					});
				}

				BX.bind(this.input, "keyup", BX.proxy(BX.SocNetLogDestination.BXfpSearch, {
					formName: this.id,
					inputName: params.inputId,
					tagInputName: params.tagId
				}));
				BX.bind(this.input, "keydown", BX.proxy(BX.SocNetLogDestination.BXfpSearchBefore, {
					formName: this.id,
					inputName: params.inputId
				}));
				BX.bind(this.input, "paste", BX.proxy(BX.SocNetLogDestination.BXfpSearch, {
					formName: this.id,
					inputName: params.inputId,
					tagInputName: params.tagId
				}));
			}

			if (this.items.hidden)
			{
				for (var ii in this.items.hidden)
				{
					if (this.items.hidden.hasOwnProperty(ii))
					{
						this.callback.select.apply(
							{
								id: (typeof this.items.hidden[ii]["PREFIX"] != 'undefined' ? this.items.hidden[ii]["PREFIX"] : 'SG') + this.items.hidden[ii]["ID"],
								name: this.items.hidden[ii]["NAME"]
							},
							(typeof this.items.hidden[ii]["TYPE"] != 'undefined' ? this.items.hidden[ii]["TYPE"] : 'sonetgroups'),
							'',
							true,
							'',
							'init'
						);
					}
				}
			}
		},

		initDialog: function(openDialogParams)
		{
			if (
				typeof openDialogParams == 'undefined'
				|| typeof openDialogParams.realParams == 'undefined'
			)
			{
				openDialogParams = null;
			}


			if (this.blockInit)
			{
				return;
			}

			var eventParams = {
				id : this.id
			};

			if (!this.initialized)
			{
				BX.onCustomEvent(window, 'BX.Main.Selector:beforeInitDialog', [ eventParams ]);
			}

			setTimeout(BX.delegate(function() {
				if (
					typeof eventParams.blockInit == 'undefined'
					|| eventParams.blockInit !== true
				)
				{
					if (this.initialized)
					{
						if (
							!this.mainPopupWindow
							|| !this.mainPopupWindow.isShown()
						)
						{
							this.openDialog(openDialogParams);
						}
					}
					else
					{
						this.getData(BX.delegate(function(data) {
							this.openDialog(openDialogParams);

							BX.onCustomEvent(window, 'BX.Main.Selector:afterInitDialog', [ {
								id: this.id
							} ]);

							if (typeof this.options.eventOpen != 'undefined')
							{
								BX.addCustomEvent(window, this.options.eventOpen, BX.delegate(function(params) {
									if (
										typeof params.id == 'undefined'
										|| params.id != this.id
									)
									{
										return;
									}

									this.openDialog({
										bindNode: params.bindNode
									});
								}, this));
							}
						}, this));
					}
				}
			}, this), 1);
		},

		openDialog: function(openDialogParams)
		{
			BX.SocNetLogDestination.openDialog(
				this.id,
				openDialogParams
			);

			this.mainPopupWindow = BX.SocNetLogDestination.popupWindow;
		},

		getData: function(callback)
		{
			this.blockInit = true;
			BX.ajax({
				url: '/bitrix/components/bitrix/main.ui.selector/ajax.php',
				method: 'POST',
				dataType: 'json',
				data: {
					sessid : BX.bitrix_sessid(),
					site : BX.message('SITE_ID'),
					options: this.options,
					action: 'getData'
				},
				onsuccess: BX.delegate(function(data) {
					this.blockInit = false;
					if (!!data.SUCCESS)
					{
						this.addData(data.DATA, callback);
						this.initialized = true;
					}
				}, this),
				onfailure: BX.delegate(function(data) {
					this.blockInit = false;
				}, this)
			});
		},

		addData: function(data, callback)
		{
			function __mergeData(target, source)
			{
				if (typeof source != 'undefined')
				{
					if (typeof target == 'undefined')
					{
						target = {};
					}
					for (var key in source)
					{
						if (source.hasOwnProperty(key))
						{
							target[key] = source[key];
						}
					}
				}
			}

			__mergeData(BX.SocNetLogDestination.obItems[this.id]['users'], data.ITEMS.USERS);
			__mergeData(BX.SocNetLogDestination.obItems[this.id]['sonetgroups'], data.ITEMS.SONETGROUPS);
			__mergeData(BX.SocNetLogDestination.obItems[this.id]['department'], data.ITEMS.DEPARTMENT);
			BX.SocNetLogDestination.obItems[this.id]["departmentRelation"] = BX.SocNetLogDestination.buildDepartmentRelation(BX.SocNetLogDestination.obItems[this.id]['department']);

			BX.SocNetLogDestination.obItemsLast[this.id]['users'] = (typeof data['ITEMS_LAST']['USERS'] != 'undefined' ? data['ITEMS_LAST']['USERS'] : {});
			BX.SocNetLogDestination.obItemsLast[this.id]['sonetgroups'] = (typeof data['ITEMS_LAST']['SONETGROUPS'] != 'undefined' ? data['ITEMS_LAST']['SONETGROUPS'] : {});
			BX.SocNetLogDestination.obItemsLast[this.id]['department'] = (typeof data['ITEMS_LAST']['DEPARTMENT'] != 'undefined' ? data['ITEMS_LAST']['DEPARTMENT'] : {});
			BX.SocNetLogDestination.obItemsLast[this.id]['groups'] = (typeof data['ITEMS_LAST']['GROUPS'] != 'undefined' ? data['ITEMS_LAST']['GROUPS'] : {});

			if (
				typeof data.ITEMS_LAST.CRM != 'undefined'
				&& data.ITEMS_LAST.CRM.length > 0
			)
			{
				BX.SocNetLogDestination.obCrmFeed[this.id] = true;
			}
			if (
				typeof data.SONETGROUPS_LIMITED != 'undefined'
				&& data.SONETGROUPS_LIMITED == 'Y'
			)
			{
				BX.SocNetLogDestination.obAllowSonetGroupsAjaxSearch[this.id] = true;
			}

			BX.SocNetLogDestination.obDestSort[this.id] = data.DEST_SORT;

			callback.apply(this, data);
		},

		getId: function()
		{
			return this.id;
		},
		getOption: function(optionId)
		{
			return (
				typeof this.options[optionId] != 'undefined'
					? this.options[optionId]
					: null
			);
		}
	};
})();