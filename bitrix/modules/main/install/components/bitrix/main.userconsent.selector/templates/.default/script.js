var MainUserConsentSelectorManager = function(params)
{
	this.selectors = [];
	this.init = function (params)
	{
		this.actionRequestUrl = params.actionRequestUrl;
		this.initSlider();

		var contexts = document.querySelectorAll('[data-bx-user-consent-selector]');
		contexts = BX.convert.nodeListToArray(contexts);
		contexts.forEach(this.initByContext, this);
	};

	this.initByContext = function(context)
	{
		var selector = context.querySelector('select[data-bx-selector]');
		var linkEdit = context.querySelector('a[data-bx-link-edit]');
		var linkView = context.querySelector('a[data-bx-link-view]');
		if (!selector || !linkEdit || !linkView)
		{
			return;
		}

		this.selectors.push(selector);
		BX.bind(selector, 'change', this.onChange.bind(this, selector, linkEdit, linkView));
		this.onChange(selector, linkEdit, linkView);

		this.initSliderButtons(context);
	};

	this.onChange = function(selector, linkEdit, linkView)
	{
		linkEdit.style.display = selector.value ? '' : 'none';
		linkView.style.display = selector.value ? '' : 'none';

		this.fillHrefByTemplate(linkEdit, selector.value);
		this.fillHrefByTemplate(linkView, selector.value);
	};

	this.fillHrefByTemplate = function(a, value)
	{
		var path = a.getAttribute('data-bx-link-tmpl');
		if (!path)
		{
			return;
		}
		a.href = path.replace(new RegExp('#id#', 'g'), value);
	};

	this.fillDropDownControl = function(node, items, skipExistedFirstElement)
	{
		items = items || [];
		var firstChildElement = node.children[0];
		node.innerHTML = '';

		if (skipExistedFirstElement && firstChildElement)
		{
			node.appendChild(firstChildElement);
		}

		items.forEach(function(item){
			if(!item || !item.caption)
			{
				return;
			}

			var option = document.createElement('option');
			option.value = item.value;
			option.selected = !!item.selected;
			option.innerText = item.caption;
			node.appendChild(option);
		});
	};

	this.initSliderButtons = function(context)
	{
		var list = context.querySelectorAll('[data-bx-slider-href]');
		list = BX.convert.nodeListToArray(list);
		list.forEach(this.slider.bindOpen, this.slider);
	};

	this.initSlider = function()
	{
		if (!top.BX.Bitrix24 || !top.BX.Bitrix24.Slider)
		{
			return;
		}


		this.slider.caller = this;
		top.BX.addCustomEvent(top, 'main-user-consent-to-list', function () {
			if (!top || !top.BX || !top.BX.Bitrix24 || !top.BX.Bitrix24.Slider)
			{
				return;
			}

			top.BX.Bitrix24.Slider.close();
		});
	};

	this.onSliderClose = function()
	{
		this.sendActionRequest('getAgreements', {}, function (data) {
			if (!data.list)
			{
				return;
			}
			this.selectors.forEach(function (selectorNode) {
				var selectedValue = selectorNode.value;
				if (!selectedValue)
				{
					selectedValue = data.list[0].ID;
				}
				var items = data.list.map(function (item) {
					return {
						caption: item.NAME,
						value: item.ID,
						selected: item.ID == selectedValue
					};
				});
				this.fillDropDownControl(selectorNode, items, true);
				BX.fireEvent(selectorNode, 'change');
			}, this);
		});
	};

	this.slider = {
		caller: null,
		init: function (params)
		{
			if (!top.BX.Bitrix24 || !top.BX.Bitrix24.Slider)
			{
				return;
			}

			top.BX.Bitrix24.PageSlider.bindAnchors({
				rules: [
					{
						condition: params.condition,
						loader: params.loader,
						stopParameters: []
					}
				]
			});
		},
		onSaved: function ()
		{
			if (!top || !top.BX || !top.BX.Bitrix24 || !top.BX.Bitrix24.Slider)
			{
				return;
			}

			this.onClose();

			top.BX.Bitrix24.Slider.close();
			top.BX.Bitrix24.Slider.resetLastOpenPage();
			top.BX.Bitrix24.Slider.removeOpenPage();
		},
		onClose: function ()
		{
			if (this.caller && this.caller.onSliderClose)
			{
				this.caller.onSliderClose.apply(this.caller);
			}
		},
		bindOpen: function (element)
		{
			if (!top.BX.Bitrix24 || !top.BX.Bitrix24.Slider)
			{
				return;
			}

			BX.bind(element, 'click', this.openHref.bind(this, element));
		},
		openHref: function (a, e)
		{
			e.preventDefault();
			this.open(a.getAttribute('href'), a.getAttribute('data-bx-slider-reload'));
		},
		open: function (url, reloadAfterClosing)
		{
			if (!top || !top.BX || !top.BX.Bitrix24 || !top.BX.Bitrix24.Slider)
			{
				return;
			}

			top.BX.Bitrix24.Slider.open(url);
			if (reloadAfterClosing)
			{
				var loadHandler = function () {
					if (!top || !top.BX || !top.BX.Bitrix24 || !top.BX.Bitrix24.Slider || !top.BX.Bitrix24.Slider.getCurrentPage())
					{
						return;
					}
					var w = top.BX.Bitrix24.Slider.getCurrentPage().getWindow();
					w.BX.addCustomEvent(w, "BX.Bitrix24.PageSlider:onClose", this.onClose.bind(this));
				};
				BX.bind(top.BX.Bitrix24.Slider.getCurrentPage().iframe, 'load', loadHandler.bind(this));
				top.BX.addCustomEvent(top, 'main-user-consent-saved', this.onSaved.bind(this));
			}
		}
	};

	this.sendActionRequest = function (action, sendData, callbackSuccess, callbackFailure)
	{
		callbackSuccess = callbackSuccess || null;
		callbackFailure = callbackFailure || null;

		sendData.action = action;
		sendData.sessid = BX.bitrix_sessid();
		sendData.action = action;

		BX.ajax({
			url: this.actionRequestUrl,
			method: 'POST',
			data: sendData,
			timeout: 10,
			dataType: 'json',
			processData: true,
			onsuccess: BX.proxy(function(data){
				data = data || {};
				if(data.error)
				{
					callbackFailure.apply(this, [data]);
				}
				else if(callbackSuccess)
				{
					callbackSuccess.apply(this, [data]);
				}
			}, this),
			onfailure: BX.proxy(function(){
				var data = {'error': true, 'text': ''};
				if (callbackFailure)
				{
					callbackFailure.apply(this, [data]);
				}
			}, this)
		});
	};

	this.init(params);
};