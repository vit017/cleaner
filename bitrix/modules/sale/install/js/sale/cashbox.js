/**
 * Class BX.Sale.Cashbox
 */
(function(window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (BX.Sale.Cashbox)
		return;

	BX.Sale.Cashbox = {

		ajaxUrl: "/bitrix/admin/sale_cashbox_ajax.php",

		init: function ()
		{
			this.toggleKkmList();
		},

		getRestrictionParamsHtml: function (params)
		{
			if (!params.class)
				return;

			params.params = params.params || {};
			params.restrictionId = params.restrictionId || 0;
			params.sort = params.sort || 100;

			ShowWaitWindow();

			var postData = {
				action: "get_restriction_params_html",
				className: params.class,
				params: params.params,
				cashboxId: params.cashboxId,
				sort: params.sort,
				lang: params.lang,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && result.RESTRICTION_HTML && !result.ERROR)
					{
						var data = BX.processHTML(result.RESTRICTION_HTML);
						BX.Sale.Cashbox.showRestrictionParamsDialog(data['HTML'], params);
						window["cashboxGetRestrictionHtmlScriptsLoadingStarted"] = false;

						//process scripts
						var scrs = function (loadScripts)
						{
							if (!loadScripts)
								BX.removeCustomEvent('cashboxGetRestrictionHtmlScriptsReady', scrs);

							for (var i in data['SCRIPT'])
							{
								BX.evalGlobal(data['SCRIPT'][i]['JS']);
								delete(data['SCRIPT'][i]);

								//It can be nesessary  at first to load some JS for restriction form
								if (loadScripts && window["cashboxGetRestrictionHtmlScriptsLoadingStarted"])
									return;
							}
						};

						BX.addCustomEvent('cashboxGetRestrictionHtmlScriptsReady', scrs);
						scrs(true);
						BX.loadCSS(data['STYLE']);
					}
					else if (result && result.ERROR)
					{
						BX.debug("Error receiving restriction params html: " + result.ERROR);
					}
					else
					{
						BX.debug("Error receiving restriction params html!");
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
					BX.debug("Error adding restriction!");
				}
			});
		},

		showRestrictionParamsDialog: function (content, rstrParams)
		{
			var width = 460,
				dialog = new BX.CDialog({
					'content': '<form id="sale-cashbox-restriction-edit-form">' +
					content +
					'</form>',
					'title': BX.message("SALE_RDL_RESTRICTION") + ": " + rstrParams.title,
					'width': width,
					'height': 500,
					'resizable': true
				});

			dialog.ClearButtons();
			dialog.SetButtons([
				{
					'title': BX.message("SALE_RDL_SAVE"),
					'action': function ()
					{

						var form = BX("sale-cashbox-restriction-edit-form"),
							prepared = BX.ajax.prepareForm(form),
							values = !!prepared && prepared.data ? prepared.data : {};

						BX.Sale.Cashbox.saveRestriction(rstrParams, values);
						this.parentWindow.Close();
					}
				},
				BX.CDialog.prototype.btnCancel
			]);

			BX.addCustomEvent(dialog, 'onWindowClose', function (dialog)
			{
				dialog.DIV.parentNode.removeChild(dialog.DIV);
			});

			dialog.Show();
			dialog.adjustSizeEx();
		},

		saveRestriction: function (rstrParams, values)
		{
			ShowWaitWindow();

			var params = values.RESTRICTION || {},
				postData = {
					action: "save_restriction",
					params: params,
					sort: values.SORT,
					className: rstrParams.class,
					cashboxId: rstrParams.cashboxId,
					restrictionId: rstrParams.restrictionId,
					sessid: BX.bitrix_sessid(),
					lang: BX.message('LANGUAGE_ID')
				};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && !result.ERROR)
					{
						if (result.HTML)
							BX.Sale.Cashbox.insertAjaxRestrictionHtml(result.HTML);
					}
					else
					{
						alert(result.ERROR);
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
				}
			});
		},

		deleteRestriction: function (restrictionId, cashboxId)
		{
			if (!restrictionId)
				return;

			ShowWaitWindow();

			var postData = {
				action: "delete_restriction",
				restrictionId: restrictionId,
				cashboxId: cashboxId,
				sessid: BX.bitrix_sessid(),
				lang: BX.message('LANGUAGE_ID')
			};

			BX.ajax({
				timeout: 30,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				data: postData,

				onsuccess: function (result)
				{
					CloseWaitWindow();

					if (result && !result.ERROR)
					{
						if (result.HTML)
							BX.Sale.Cashbox.insertAjaxRestrictionHtml(result.HTML);

						if (result.ERROR)
							BX.debug("Error deleting restriction: " + result.ERROR);
					}
					else
					{
						BX.debug("Error deleting restriction!");
					}
				},

				onfailure: function ()
				{
					CloseWaitWindow();
					BX.debug("Error refreshing restriction!");
				}
			});
		},

		insertAjaxRestrictionHtml: function (html)
		{
			var data = BX.processHTML(html),
				container = BX("sale-cashbox-restriction-container");

			if (!container)
				return;

			BX.loadCSS(data['STYLE']);

			container.innerHTML = data['HTML'];

			for (var i in data['SCRIPT'])
				BX.evalGlobal(data['SCRIPT'][i]['JS']);
		},

		generateConnectionLink: function()
		{

			var data = {
				'action': 'generate_link',
				'sessid': BX.bitrix_sessid()
			};

			BX.showWait();
			BX.ajax({
				data: data,
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
				{
						BX.closeWait();
						if(result)
						{
							if(!result.ERROR)
							{
								text =
									'<div style="margin-bottom: 50px;">' +
										'<ul class="adm-cashbox-list2 adm-cashbox-inner">' +
											'<li style="margin-bottom: 20px;">' + BX.message('SALE_CASHBOX_WINDOW_STEP_1') + '<br> <b id="generated-link">' + result.LINK + '</b></li>' +
											'<li>' + BX.message('SALE_CASHBOX_WINDOW_STEP_2') + '</li>' +
										'</ul>' +
									'</div>';
								var dlg = new BX.CAdminDialog({
									'content': text,
									'title': BX.message('SALE_CASHBOX_WINDOW_TITLE'),
									'resizable': false,
									'draggable': false,
									'height': '145',
									'width': '516',
									'buttons': [
										{
											title: top.BX.message('SALE_CASHBOX_COPY'),
											id: 'copyCheckBtn',
											name: 'copybtn',
											className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
										},
										BX.CAdminDialog.btnCancel
									]
								});
								dlg.Show();
								var copy = BX('copyCheckBtn');
								if (copy)
									BX.clipboard.bindCopyClick(copy, {text : result.LINK});
							}
							else
							{
								BX.debug(result.ERROR);
							}
						}
					}, this
				),
				onfailure: function() {BX.debug('onfailure: generateConnectionLink');}
			});
		},

		connectToKKM: function (event)
		{
			BX.ajax({
				data: {
					'action': 'generate_link',
					'sessid': BX.bitrix_sessid()
				},
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
				{
					BX.closeWait();
					if(result)
					{
						if(!result.ERROR)
						{
							var parent = event.parentNode;
							BX.hide(parent);
							var container = BX('container-instruction');
							container.style.display = 'block';
							BX('cashbox-url').innerHTML = result.LINK;
						}
						else
						{
							BX.debug(result.ERROR);
						}
					}
				}, this
				),
				onfailure: function() {BX.debug('onfailure: generateConnectionLink');}
			});
		},

		toggleKkmList: function()
		{
			var handler = BX('HANDLER').value;
			var tr = BX('KKM_ID').parentNode.parentNode;
			if (tr)
			{
				if (handler === '\\Bitrix\\Sale\\Cashbox\\CashboxBitrix')
				{
					tr.style.display = 'table-row';
					BX('KKM_ID').disabled = false;
				}
				else
				{
					tr.style.display = 'none';
					BX('KKM_ID').disabled = true;
				}
			}
		},

		reloadSettings: function()
		{
			this.toggleKkmList();

			BX.ajax({
				data: {
					'action': 'reload_settings',
					'kkmId': BX('KKM_ID').value || 0,
					'handler': BX('HANDLER').value || '',
					'sessid': BX.bitrix_sessid()
				},
				method: 'POST',
				dataType: 'json',
				url: this.ajaxUrl,
				onsuccess: BX.delegate(function(result)
					{
						BX.closeWait();
						if (result && result.hasOwnProperty('HTML'))
							BX('sale-cashbox-settings-container').innerHTML = result.HTML;
					}, this
				),
				onfailure: function() {BX.debug('onfailure: reloadSettings');}
			});
		},

		showCreateCheckWindow: function(type)
		{
			var data = {
				action: 'addCheckOrder',
				type: type || null,
				returnHtml: true,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax(
			{
				method: 'post',
				dataType: 'json',
				url: '/bitrix/admin/sale_order_ajax.php',
				data: data,
				onsuccess: function(result)
				{
					if (result.ERROR && result.ERROR.length > 0)
					{
						alert(result.ERROR);
					}
					else
					{
						var dlg = new BX.CAdminDialog({
							'title': BX.message('CASHBOX_CREATE_WINDOW_TITLE'),
							'content': result.HTML,
							'resizable': false,
							'draggable': true,
							'height': '300',
							'width': '516',
							'buttons': [
								{
									title: BX.message('JS_CORE_WINDOW_SAVE'),
									id: 'saveCheckBtn',
									name: 'savebtn',
									className: top.BX.browser.IsIE() && top.BX.browser.IsDoctype() && !top.BX.browser.IsIE10() ? '' : 'adm-btn-save'
								},
								{
									title: top.BX.message('JS_CORE_WINDOW_CANCEL'),
									id: 'cancelCheckBtn',
									name: 'cancel'
								}
							]
						});
						var orderInput = BX('checkInputOrder');
						var paymentSelect = BX('checkSelectPayment');
						var shipmentSelect = BX('checkSelectShipment');
						var typeSelect = BX('checkSelectType');
						dlg.Show();
						BX.bind(BX("checkInputOrder"), 'input', BX.delegate(
							function()
							{
								var sendData = {
									sessid : BX.bitrix_sessid(),
									orderId: orderInput.value,
									paymentId: paymentSelect.value,
									shipmentId: shipmentSelect.value,
									typeId: typeSelect.value,
									action: 'addCheckOrder'
								};

								BX.ajax(
									{
										method: 'post',
										dataType: 'json',
										url: '/bitrix/admin/sale_order_ajax.php',
										data: sendData,
										onsuccess: function (result)
										{
											var htmlSelectData ='';
											if (result.ERROR && result.ERROR.length > 0)
											{
												alert(result.ERROR);
											}
											else
											{
												htmlSelectData = "<option selected='selected' value=''>"+ BX.message('CASHBOX_CREATE_WINDOW_NOT_SELECT') +"</option>";
												if (result.ORDER_DATA.PAYMENT && result.ORDER_DATA.PAYMENT.length > 0)
												{
													paymentSelect.removeAttribute('disabled');
													for (var i in result.ORDER_DATA.PAYMENT)
													{
														htmlSelectData = htmlSelectData + '<option value="'+ result.ORDER_DATA.PAYMENT[i].ID+'">'+result.ORDER_DATA.PAYMENT[i].NAME+'</option>';
													}
												}
												else
												{
													paymentSelect.setAttribute('disabled', 'disabled');
												}

												paymentSelect.innerHTML = htmlSelectData;

												htmlSelectData = "<option selected='selected' value=''>"+ BX.message('CASHBOX_CREATE_WINDOW_NOT_SELECT') +"</option>";
												if (result.ORDER_DATA.SHIPMENT && result.ORDER_DATA.SHIPMENT.length > 0)
												{
													shipmentSelect.removeAttribute('disabled');
													for (i in result.ORDER_DATA.SHIPMENT)
													{
														htmlSelectData = htmlSelectData + '<option value="'+ result.ORDER_DATA.SHIPMENT[i].ID+'">'+ result.ORDER_DATA.SHIPMENT[i].NAME+'</option>';
													}
												}
												else
												{
													shipmentSelect.setAttribute('disabled', 'disabled');
												}

												shipmentSelect.innerHTML = htmlSelectData;
											}
										},
										onfailure: function() {BX.debug('Select params error');}
									}
								);
							}
						),this);

						BX.bind(BX("cancelCheckBtn"), 'click', BX.delegate(
							function()
							{
								dlg.Close();
								dlg.DIV.parentNode.removeChild(dlg.DIV);
							}
						),this );

						BX.bind(BX("saveCheckBtn"), 'click', BX.delegate(
							function()
							{
								var sendData = {
									sessid : BX.bitrix_sessid(),
									orderId: orderInput.value,
									paymentId: paymentSelect.value,
									shipmentId: shipmentSelect.value,
									typeId: typeSelect.value,
									action: 'saveCheck'
								};
								BX.ajax(
									{
										method: 'post',
										dataType: 'json',
										url: '/bitrix/admin/sale_order_ajax.php',
										data: sendData,
										onsuccess: function (result)
										{
											if (result.ERROR && result.ERROR.length > 0)
											{
												alert(result.ERROR);
											}
											else
											{
												dlg.Close();
												location.reload();
											}
										},
										onfailure: function() {BX.debug('Select params error');}
									}
								);

							}
						),this);
					}
				},
				onfailure: function() {BX.debug('Create window error');}
			});
		}
	}
})(window);
