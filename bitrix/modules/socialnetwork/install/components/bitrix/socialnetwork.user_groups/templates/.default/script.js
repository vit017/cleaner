BitrixSUG = function ()
{

};

BitrixSUG.prototype.sendRequest = function(params)
{
	if (
		typeof params == 'undefined'
		|| typeof params.groupId == 'undefined'
		|| parseInt(params.groupId) <= 0
	)
	{
		return false;
	}

	if (
		typeof params.action == 'undefined'
		|| !BX.util.in_array(params.action, ['REQUEST', 'FAVORITES'])
	)
	{
		return false;
	}

	var requestParams = {};

	if (params.action == 'FAVORITES')
	{
		requestParams.value = (typeof params.value != 'undefined' ? params.value : 'Y');
	}

	BX.ajax({
		url: '/bitrix/components/bitrix/socialnetwork.user_groups/ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {
			sessid : BX.bitrix_sessid(),
			site : BX.message('SITE_ID'),
			groupId: parseInt(params.groupId),
			action : params.action,
			params : requestParams
		},
		onsuccess: function(responseData)
		{
			if (typeof responseData.SUCCESS != 'undefined')
			{
				params.callback_success(responseData);
			}
			else
			{
				params.callback_failure(typeof responseData.ERROR != 'undefined' ? responseData.ERROR : BX('SONET_C33_T_F_REQUEST_ERROR'));
			}
		},
		onfailure: function(responseData)
		{
			params.callback_failure(BX('SONET_C33_T_F_REQUEST_ERROR'));
		}
	});

	return false;
};

BitrixSUG.prototype.showRequestWait = function(target)
{
	BX.addClass(target, 'popup-window-button-wait');
};

BitrixSUG.prototype.closeRequestWait = function(target)
{
	BX.removeClass(target, 'popup-window-button-wait');
};

BitrixSUG.prototype.setFavorites = function(node, active)
{
	if (BX(node))
	{
		var flyingStar = node.cloneNode();
		flyingStar.style.marginLeft = "-" + node.offsetWidth + "px";
		node.parentNode.appendChild(flyingStar);

		new BX.easing({
			duration: 200,
			start: { opacity: 100, scale: 100 },
			finish: { opacity: 0, scale: 300 },
			transition : BX.easing.transitions.linear,
			step: function(state) {
				flyingStar.style.transform = "scale(" + state.scale / 100 + ")";
				flyingStar.style.opacity = state.opacity / 100;
			},
			complete: function() {
				flyingStar.parentNode.removeChild(flyingStar);
			}
		}).animate();

		if (!!active)
		{
			BX.addClass(node, 'sonet-groups-group-title-favorites-active');
			BX.adjust(node, { attrs : {title : BX.message('SONET_C33_T_ACT_FAVORITES_REMOVE')} });
		}
		else
		{
			BX.removeClass(node, 'sonet-groups-group-title-favorites-active');
			BX.adjust(node, { attrs : {title : BX.message('SONET_C33_T_ACT_FAVORITES_ADD')} });
		}
	}
};

BitrixSUG.prototype.setRequestSent = function(node, sentNode, role)
{
	if (BX(node))
	{
		this.closeRequestWait(node);
		BX.addClass(node, 'sonet-groups-group-btn-sent');
	}

	if (
		typeof role != 'undefined'
		&& role == 'Z'
		&& BX(sentNode)
	)
	{
		BX.addClass(sentNode, 'sonet-groups-group-desc-container-active');
	}
};

BitrixSUG.prototype.showRequestSent = function(sentNode)
{
	if (BX(sentNode))
	{
		BX.addClass(sentNode, 'sonet-groups-group-desc-container-active');
	}
};

BitrixSUG.prototype.showError = function(errorText)
{
	var errorPopup = new BX.PopupWindow('ug-error' + Math.random(), window, {
		autoHide: true,
		lightShadow: false,
		zIndex: 2,
		content: BX.create('DIV', {props: {'className': 'sonet-groups-error-text-block'}, html: BX.message('SONET_C33_T_F_REQUEST_ERROR').replace('#ERROR#', errorText)}),
		closeByEsc: true,
		closeIcon: true
	});
	errorPopup.show();
};

BitrixSUG.prototype.showSortMenu = function(params)
{
	BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
	BX.PopupMenu.show('bx-sonet-groups-sort-menu', params.bindNode, [
		{
			text: BX.message('SONET_C33_T_F_SORT_ALPHA'),
			onclick: BX.proxy(function() {
				this.selectSortMenuItem({
					text: BX.message('SONET_C33_T_F_SORT_ALPHA'),
					key: 'alpha',
					valueNode: params.valueNode
				});
				BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
			}, this)
		},
		{
			text: BX.message('SONET_C33_T_F_SORT_DATE_REQUEST'),
			onclick: BX.proxy(function() {
				this.selectSortMenuItem({
					text: BX.message('SONET_C33_T_F_SORT_DATE_REQUEST'),
					key: 'date_request',
					valueNode: params.valueNode
				});
				BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
			}, this)
		},
		(
			parseInt(params.userId) == BX.message('USER_ID')
				? {
					text: BX.message('SONET_C33_T_F_SORT_DATE_VIEW'),
					onclick: BX.proxy(function() {
						this.selectSortMenuItem({
							text: BX.message('SONET_C33_T_F_SORT_DATE_VIEW'),
							key: 'date_view',
							valueNode: params.valueNode
						});
						BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
					}, this)
				}
				: null
		),
		(
			params.showMembersCountItem
				? {
					text: BX.message('SONET_C33_T_F_SORT_MEMBERS_COUNT'),
					onclick: BX.proxy(function() {
						this.selectSortMenuItem({
							text: BX.message('SONET_C33_T_F_SORT_MEMBERS_COUNT'),
							key: 'members_count',
							valueNode: params.valueNode
						});
						BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
					}, this)
				}
				: null
		),
		{
			text: BX.message('SONET_C33_T_F_SORT_DATE_ACTIVITY'),
			onclick: BX.proxy(function() {
				this.selectSortMenuItem({
					text: BX.message('SONET_C33_T_F_SORT_DATE_ACTIVITY'),
					key: 'date_activity',
					valueNode: params.valueNode
				});
				BX.PopupMenu.destroy('bx-sonet-groups-sort-menu');
			}, this)
		}
	], {
		offsetLeft: 100,
		offsetTop: 0,
		lightShadow: false,
		angle: {position: 'top', offset : 50}
 	});

	return false;
};

BitrixSUG.prototype.selectSortMenuItem = function(params)
{
	BX(params.valueNode).innerHTML = params.text;
	var url = null;

	switch(params.key)
	{
		case 'alpha':
			url = BX.message('filterAlphaUrl');
			break;
		case 'date_request':
			url = BX.message('filterDateRequestUrl');
			break;
		case 'date_view':
			url = BX.message('filterDateViewUrl');
			break;
		case 'members_count':
			url = BX.message('filterMembersCountUrl');
			break;
		case 'date_activity':
			url = BX.message('filterDateActivitytUrl');
			break;
		default:
			url = BX.message('filterAlphaUrl')
	}

	document.location.href = url;
};


oSUG = new BitrixSUG;
window.oSUG = oSUG;