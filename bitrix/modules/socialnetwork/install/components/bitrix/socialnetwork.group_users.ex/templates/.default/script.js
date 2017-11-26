(function(){

	if (!!BX.BXGUE)
	{
		return;
	}

	BX.BXGUE =
	{
		menuPopup: null,
		waitPopup: null,
		waitTimeout: null,
		waitTime: 500
	};

	BX.BXGUE.toggleCheckbox = function(ev, block, user_code)
	{
		ev = ev || window.event;

		if (
			user_code == 'undefined'
			|| !user_code
		)
		{
			return false;
		}

		var type = user_code.substr(0, 1);
		var user_id_tmp = parseInt(user_code.substr(1));

		switch (type) {
			case 'M':
				if (BX.util.in_array(user_id_tmp, actionUsers['Moderators']))
					actionUsers['Moderators'].splice(BX.util.array_search(user_id_tmp, actionUsers['Moderators']), 1);
				else
					actionUsers['Moderators'][actionUsers['Moderators'].length] = user_id_tmp;
				break;
			case 'U':
				if (BX.util.in_array(user_id_tmp, actionUsers['Users']))
					actionUsers['Users'].splice(BX.util.array_search(user_id_tmp, actionUsers['Users']), 1);
				else
					actionUsers['Users'][actionUsers['Users'].length] = user_id_tmp;
				break;
			case 'A':
				if (BX.util.in_array(user_id_tmp, actionUsers['UsersAuto']))
					actionUsers['UsersAuto'].splice(BX.util.array_search(user_id_tmp, actionUsers['UsersAuto']), 1);
				else
					actionUsers['UsersAuto'][actionUsers['UsersAuto'].length] = user_id_tmp;
				break;
			case 'B':
				if (BX.message("GUEUseBan") == "Y")
				{
					if (BX.util.in_array(user_id_tmp, actionUsers['Banned']))
						actionUsers['Banned'].splice(BX.util.array_search(user_id_tmp, actionUsers['Banned']), 1);
					else
						actionUsers['Banned'][actionUsers['Banned'].length] = user_id_tmp;
				}
				break;
			case 'D':
				if (BX.util.in_array(user_id_tmp, actionUsers['Departments']))
				{
					actionUsers['Departments'].splice(BX.util.array_search(user_id_tmp, actionUsers['Departments']), 1);
				}
				else
				{
					actionUsers['Departments'][actionUsers['Departments'].length] = user_id_tmp;
				}
				break;
			default:
				return false;
		}

		var check_box = BX.findChild(block, { tagName: 'input' }, true, false);

		if (ev.target == check_box || ev.srcElement == check_box)
		{
			BX.toggleClass(block.parentNode, 'sonet-members-member-block-active');
			return false;
		}
		else
		{
			BX.toggleClass(block.parentNode, 'sonet-members-member-block-active');
			check_box.checked = (check_box.checked != true);
		}

		BX.PreventDefault(ev);
	};

	BX.BXGUE.showMenu = function(bindElement, type, invitePopupName)
	{
		if (!type)
		{
			type = 'users';
		}

		var arItems = [];

		if (type == 'users')
		{
			if (BX.message("GUEUserCanInitiate"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUEAddToUsersTitle'),
					className : "menu-popup-no-icon",
					onclick : function(e) {
						if (BX.SGCP)
						{
							BX.SGCP.ShowForm('invite', invitePopupName, e);
						}
						return BX.PreventDefault(e);
					}
				};
			}

			if (BX.message("GUEUserCanModifyGroup"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUEAddToModeratorsTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.sendAjax({
							popup : this.menuPopup.popupWindow,
							action : 'U2M',
							items: BX.util.array_merge(actionUsers['Users'], actionUsers['UsersAuto'])
						});
						return BX.PreventDefault(e);
					}, this)
				};
				arItems[arItems.length] = {
					text : BX.message('GUEExcludeFromGroupTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						if(confirm(BX.message('GUEExcludeFromGroupConfirmTitle')))
						{
							this.sendAjax({
								popup : this.menuPopup.popupWindow,
								action : 'EX',
								items: BX.util.array_merge(actionUsers['Moderators'], actionUsers['Users'])
							});
						}
						return BX.PreventDefault(e);
					}, this)
				};
				arItems[arItems.length] = {
					text : BX.message('GUESetGroupOwnerTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.setGroupOwner(this.menuPopup.popupWindow);
						return BX.PreventDefault(e);
					}, this)
				};
			}
		}
		else if (type == 'users_auto')
		{
			if (BX.message("GUEUserCanInitiate"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUEAddToUsersTitle'),
					className : "menu-popup-no-icon",
					onclick : function(e) {
						if (BX.SGCP)
						{
							BX.SGCP.ShowForm('invite', invitePopupName, e);
						}
						return BX.PreventDefault(e);
					}
				};
			}

			if (BX.message("GUEUserCanModifyGroup"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUEAddToModeratorsTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.sendAjax({
							popup : this.menuPopup.popupWindow,
							action : 'U2M',
							items: BX.util.array_merge(actionUsers['Users'], actionUsers['UsersAuto'])
						});
						return BX.PreventDefault(e);
					}, this)
				};
				arItems[arItems.length] = {
					text : BX.message('GUESetGroupOwnerTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.setGroupOwner(this.menuPopup.popupWindow);
						return BX.PreventDefault(e);
					}, this)
				};
			}
		}
		else if (type == 'moderators')
		{
			if (BX.message("GUEUserCanModifyGroup"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUEExcludeFromModeratorsTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.sendAjax({
							popup : this.menuPopup.popupWindow,
							action : 'M2U',
							items: actionUsers['Moderators']
						});
						return BX.PreventDefault(e);
					}, this)
				};
				arItems[arItems.length] = {
					text : BX.message('GUEExcludeFromGroupTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.sendAjax({
							popup : this.menuPopup.popupWindow,
							action : 'EX',
							items: BX.util.array_merge(actionUsers['Moderators'], actionUsers['Users'])
						});
						return BX.PreventDefault(e);
					}, this)
				};
				arItems[arItems.length] = {
					text : BX.message('GUESetGroupOwnerTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.setGroupOwner(this.menuPopup.popupWindow);
						return BX.PreventDefault(e);
					}, this)
				};
			}
		}
		else if (type == 'ban')
		{
			if (BX.message("GUEUserCanModerateGroup"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUEUnBanFromGroupTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.sendAjax({
							popup : this.menuPopup.popupWindow,
							action : 'UNBAN',
							items: actionUsers['Banned']
						});
						return BX.PreventDefault(e);
					}, this)
				};
			}
		}
		else if (type == 'departments')
		{
			if (BX.message("GUEUserCanModifyGroup"))
			{
				arItems[arItems.length] = {
					text : BX.message('GUESetGroupUnconnectDeptTitle'),
					className : "menu-popup-no-icon",
					onclick : BX.proxy(function(e) {
						this.sendAjax({
							popup : this.menuPopup.popupWindow,
							action : 'UNCONNECT_DEPT',
							items: actionUsers['Departments']
						});
						return BX.PreventDefault(e);
					}, this)
				};
			}
		}

		if (arItems.length > 0)
		{
			var arParams = {
				offsetLeft: -32,
				offsetTop: 4,
				lightShadow: false,
				angle: {position: 'top', offset : 60}
			};

			this.menuPopup = BX.PopupMenu.create("gue-menu-" + type, bindElement, arItems, arParams);
			this.menuPopup.popupWindow.show();
		}
	};

	BX.BXGUE.sendAjax = function(data)
	{
		if (data.items.length > 0)
		{
			this.showWait();
			var requestData = {
				ACTION: data.action,
				GROUP_ID: parseInt(BX.message('GUEGroupId')),
				sessid: BX.bitrix_sessid(),
				site: BX.util.urlencode(BX.message('GUESiteId'))
			};

			if (data.action == 'UNCONNECT_DEPT')
			{
				requestData.DEPARTMENT_ID = data.items;
			}
			else
			{
				requestData.USER_ID = data.items;
			}

			BX.ajax({
				url: '/bitrix/components/bitrix/socialnetwork.group_users.ex/ajax.php',
				method: 'POST',
				dataType: 'json',
				data: requestData,
				onsuccess: BX.proxy(function(responseData) {
					this.processAJAXResponse(responseData, data.popup);
				}, this)
			});
		}
		else
		{
			this.showError(BX.message(data.action == 'UNCONNECT_DEPT' ? 'GUEErrorDepartmentIDNotDefined' : 'GUEErrorUserIDNotDefined'));
		}
	};

	BX.BXGUE.setGroupOwner = function(popup)
	{
		if (
			actionUsers['Moderators'].length === 1
			|| actionUsers['Users'].length === 1
			|| actionUsers['UsersAuto'].length === 1
		)
		{
			var newOwnerID = parseInt(
				actionUsers['Moderators'].length === 1
					? actionUsers['Moderators'][0]
					: actionUsers['Users'].length === 1
						? actionUsers['Users'][0]
						: actionUsers['UsersAuto'][0]
			);
			if (
				typeof oldOwnerID != 'undefined'
				&& newOwnerID == parseInt(oldOwnerID)
			)
			{
				this.showError(BX.message('GUEErrorSameOwner'));
			}
			else if(confirm(BX.message('GUESetGroupOwnerConfirmTitle')))
			{
				this.showWait();
				BX.ajax({
					url: '/bitrix/components/bitrix/socialnetwork.group_users.ex/ajax.php',
					method: 'POST',
					dataType: 'json',
					data: {
						'ACTION': 'SETOWNER',
						'GROUP_ID': parseInt(BX.message('GUEGroupId')),
						'USER_ID' : [ newOwnerID ],
						'sessid': BX.bitrix_sessid(),
						'site': BX.util.urlencode(BX.message('GUESiteId'))
					},
					onsuccess: BX.proxy(function(data) {
						this.processAJAXResponse(data, popup);
					}, this)
				});
			}
		}
		else
		{
			this.showError(BX.message('GUEErrorUserIDIncorrect'));
		}
	};

	BX.BXGUE.processAJAXResponse = function(data, popup)
	{
		if (popup == 'undefined' || popup == null || !popup.isShown())
			return false;

		if (data["SUCCESS"] != "undefined" && data["SUCCESS"] == "Y")
		{
			popup.close();
			BX.reload();
		}
		else if (data["ERROR"] != "undefined" && data["ERROR"].length > 0)
		{
			if (data["ERROR"].indexOf("USER_ACTION_FAILED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorActionFailedPattern').replace("#ERROR#", data["ERROR"].substr(20)));
				return false;
			}
			else if (data["ERROR"].indexOf("SESSION_ERROR", 0) === 0)
			{
				this.showError(BX.message('GUEErrorSessionWrong'));
				BX.reload();
			}
			else if (data["ERROR"].indexOf("USER_GROUP_NO_PERMS", 0) === 0)
			{
				this.showError(BX.message('GUEErrorNoPerms'));
				return false;
			}
			else if (data["ERROR"].indexOf("USER_ID_NOT_DEFINED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorUserIDNotDefined'));
				return false;
			}
			else if (data["ERROR"].indexOf("DEPARTMENT_ID_NOT_DEFINED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorDepartmentIDNotDefined'));
				return false;
			}
			else if (data["ERROR"].indexOf("GROUP_ID_NOT_DEFINED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorGroupIDNotDefined'));
				return false;
			}
			else if (data["ERROR"].indexOf("CURRENT_USER_NOT_AUTH", 0) === 0)
			{
				this.showError(BX.message('GUEErrorCurrentUserNotAuthorized'));
				return false;
			}
			else if (data["ERROR"].indexOf("SONET_MODULE_NOT_INSTALLED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorModuleNotInstalled'));
				return false;
			}
			else if (data["ERROR"].indexOf("SONET_GUE_T_OWNER_CANT_EXCLUDE_HIMSELF", 0) === 0)
			{
				this.showError(BX.message('GUEErrorOwnerCantExcludeHimself'));
				return false;
			}
			else if (data["ERROR"].indexOf("SONET_GUE_T_CANT_EXCLUDE_AUTO_MEMBER", 0) === 0)
			{
				this.showError(BX.message('GUEErrorCantExcludeAutoMember'));
				return false;
			}
			else if (data["ERROR"].indexOf("DEPARTMENT_ACTION_FAILED", 0) === 0)
			{
				this.showError(BX.message('GUEErrorActionFailedPattern').replace("#ERROR#", data["ERROR"].substr(26)));
				return false;
			}
			else
			{
				this.showError(data["ERROR"]);
				return false;
			}
		}
	};

	BX.BXGUE.showError = function(errorText)
	{
		this.closeWait();
		var errorPopup = new BX.PopupWindow('gue-error' + Math.random(), window, {
			autoHide: true,
			lightShadow: false,
			zIndex: 2,
			content: BX.create('DIV', {props: {'className': 'sonet-members-error-text-block'}, html: errorText}),
			closeByEsc: true,
			closeIcon: true
		});
		errorPopup.show();
	};

	BX.BXGUE.showWait = function(timeout)
	{
		if (timeout !== 0)
		{
			return (this.waitTimeout = setTimeout(BX.proxy(function(){
				this.showWait(0)
			}, this), 50));
		}

		if (!this.waitPopup)
		{
			this.waitPopup = new BX.PopupWindow('gue_wait', window, {
				autoHide: true,
				lightShadow: true,
				zIndex: 2,
				content: BX.create('DIV', {
					props: {
						className: 'sonet-members-wait-cont'
					},
					children: [
						BX.create('DIV', {
							props: {
								className: 'sonet-members-wait-icon'
							}
						}),
						BX.create('DIV', {
							props: {
								className: 'sonet-members-wait-text'
							},
							html: BX.message('GUEWaitTitle')
						})
					]
				})
			});
		}
		else
		{
			this.waitPopup.setBindElement(window);
		}

		this.waitPopup.show();
	};

	BX.BXGUE.closeWait = function()
	{
		if (this.waitTimeout)
		{
			clearTimeout(this.waitTimeout);
			this.waitTimeout = null;
		}

		if (this.waitPopup)
		{
			this.waitPopup.close();
		}
	}
})();