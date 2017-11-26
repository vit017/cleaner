;(function (window) {

	if (!BX.Sale)
		BX.Sale = {};

	if (!BX.Sale.VkAdmin) {
		BX.Sale.VkAdmin = {};

	}
	BX.Sale.VkAdmin = {

		ajaxUrl: '/bitrix/admin/sale_vk_ajax.php',

		startFeed: function (feedType, exportId, firstRun) {
			BX.Sale.VkAdmin.exportProcessProlog();

			if(firstRun)
				BX.cleanNode(BX('vk_export_notify__progress'));

			BX.showWait();

			var postData = {
				action: "startFeed",
				type: feedType,
				exportId: exportId,
				firstRun: firstRun,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 120,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.VkAdmin.ajaxUrl,
				data: postData,

				onsuccess: function (result) {
					// reload page to show ERRORS
					if (result && result.ERRORS_CRITICAL) {
						BX.closeWait();
						BX.adjust(BX('vk_export_notify__error_critical'), {style: {display: 'block'}});
						BX.adjust(BX('vk_export_notify__error_critical'), {html: result.ERRORS_CRITICAL});
						BX.Sale.VkAdmin.exportProcessEpilog();
					}

					// NOT error
					else {
						// NORMAL mode, continue process
						if (result && result.CONTINUE) {
							BX.closeWait();
							BX.Sale.VkAdmin.startFeed(result.TYPE, exportId, false);
						}
						// FINISH process
						else {
							BX.closeWait();
							BX.Sale.VkAdmin.exportProcessEpilog();
						}

						if (result.PROGRESS.length > 0)
							BX.adjust(BX('vk_export_notify__progress'), {html: result.PROGRESS});

						if (typeof(result.STATS_ALBUMS) != 'undefined' && result.STATS_ALBUMS.length > 0)
							BX.adjust(BX('vk_export_statistic__albums'), {html: result.STATS_ALBUMS});

						if (typeof(result.STATS_PRODUCTS) != 'undefined' && result.STATS_PRODUCTS.length > 0)
							BX.adjust(BX('vk_export_statistic__products'), {html: result.STATS_PRODUCTS});

						if (typeof(result.ERRORS_NORMAL) != 'undefined' && result.ERRORS_NORMAL.length > 0) {
							BX.adjust(BX('vk_export_notify__error_normal'), {style: {display: 'block'}});
							BX.adjust(BX('vk_export_notify__error_normal__msg'), {html: result.ERRORS_NORMAL});
						}
					}
				},

				onfailure: function () {
					BX.closeWait();
					BX.Sale.VkAdmin.exportProcessEpilog();
					BX.debug('Feed failure!');
					location.reload();
				}
			});
		},


		exportProcessProlog: function () {
			BX.adjust(BX('vk_export_button__startFeed_all'), {props: {disabled: true}});
			BX.adjust(BX('vk_export_button__startFeed_add'), {style: {display: 'none'}});
			BX.adjust(BX('vk_export_button__startFeed_delete'), {style: {display: 'none'}});

			BX.addClass(BX('vk_export_button__startFeed_add'), 'adm-btn-disabled');
			BX.addClass(BX('vk_export_button__startFeed_delete'), 'adm-btn-disabled');
			// maybe we can disable onclick on buttons without hide them? see repo

			BX.adjust(BX('vk_export_button__save'), {props: {disabled: true}});
			BX.adjust(BX('vk_export_button__apply'), {props: {disabled: true}});
			BX.adjust(BX('vk_export_button__cancel'), {props: {disabled: true}});
		},


		exportProcessEpilog: function () {
			BX.adjust(BX('vk_export_button__startFeed_all'), {props: {disabled: false}});
			BX.adjust(BX('vk_export_button__startFeed_add'), {style: {display: 'inline-block'}});
			BX.adjust(BX('vk_export_button__startFeed_delete'), {style: {display: 'inline-block'}});

			BX.removeClass(BX('vk_export_button__startFeed_add'), 'adm-btn-disabled');
			BX.removeClass(BX('vk_export_button__startFeed_delete'), 'adm-btn-disabled');
			// maybe we can disable onclick on buttons without hide them? see repo

			BX.adjust(BX('vk_export_button__save'), {props: {disabled: false}});
			BX.adjust(BX('vk_export_button__apply'), {props: {disabled: false}});
			BX.adjust(BX('vk_export_button__cancel'), {props: {disabled: false}});
		},


		stopProcess: function (exportId) {
			BX.showWait();

			var postData = {
				action: "stopProcess",
				exportId: exportId,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 120,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.VkAdmin.ajaxUrl,
				data: postData,

				onsuccess: function (result) {
					BX.closeWait();

					if (result && result.COMPLETED) {
						//all right
					}
					else {
						alert(result.ERROR);
					}

					BX.Sale.VkAdmin.exportProcessEpilog();
				},

				onfailure: function () {
					BX.debug('Feed SALE_VK_SETTINGS_RESET_ERROR');
				}
			});
		},

		clearErrorLog: function (exportId) {
			BX.showWait();

			var postData = {
				action: "clearErrorLog",
				exportId: exportId,
				sessid: BX.bitrix_sessid()
			};

			BX.ajax({
				timeout: 120,
				method: 'POST',
				dataType: 'json',
				url: BX.Sale.VkAdmin.ajaxUrl,
				data: postData,

				onsuccess: function (result) {
					BX.closeWait();

					if (result && result.COMPLETED) {
						BX.adjust(BX('vk_export_notify__error_normal'), {style: {display: 'none'}});
						BX.adjust(BX('vk_export_notify__error_critical'), {style: {display: 'none'}});
						//all right, do nothing
					}
					else {
						alert(result.MESSAGE);
					}
				},

				onfailure: function () {
					BX.debug('Feed SALE_VK_SETTINGS_RESET_ERROR');
				}
			});
		}
	};
})(window);