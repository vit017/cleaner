<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?if ($arResult["PLAYER_TYPE"] == "flv"): // Attach Flash Player?>

	<div id="<?=$arResult["ID"]?>_div" style="width: <?= $arParams['WIDTH']?>px; height: <?= $arParams['HEIGHT']?>px;"><?= GetMessage('PLAYER_LOADING')?></div>
	<script>
		window.bxPlayerOnload<?=$arResult["ID"]?> = function(config)
		{
			if (typeof config != 'object')
				config = <?= $arResult['jwConfig']?>;

			jwplayer("<?=$arResult["ID"]?>_div").setup(config);

			<?if (isset($arParams['WMODE']) && $arParams['WMODE'] != 'opaque'):?>
			jwplayer("<?=$arResult["ID"]?>_div").onReady(function()
			{
				try{
					var pWmode = BX.findChild(BX("<?=$arResult["ID"]?>_div"), {tagName: "PARAM", attribute: {name: "wmode"}});
					if (pWmode)
						pWmode.value = "<?= $arParams['WMODE']?>";

					var pEmbed = BX.findChild(BX("<?=$arResult["ID"]?>_div"), {tagName: "EMBED"});
					if (pEmbed && pEmbed.setAttribute)
						pEmbed.setAttribute("wmode", "<?= $arParams['WMODE']?>");
				}catch(e){}
			});
			<?endif;?>
		};

		if (window.jwplayer) // jw script already loaded
		{
			setTimeout(bxPlayerOnload<?=$arResult["ID"]?>, 100);
		}
		else
		{
			BX.addCustomEvent(window, "onPlayerJWScriptLoad", function(){setTimeout(bxPlayerOnload<?=$arResult["ID"]?>, 100);});
			if (!window.bPlayerJWScriptLoaded)
			{
				window.bPlayerJWScriptLoaded = true;
				// load jw scripts once
				BX.loadScript('<?=$this->__component->__path;?>/mediaplayer/jwplayer.js', function(){setTimeout(function()
				{
					BX.onCustomEvent(window, "onPlayerJWScriptLoad");
				}, 100);});
			}
		}
	</script><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>

<?elseif ($arResult["PLAYER_TYPE"] == "wmv"): // Attach WMV Player?>
	<div id="<?=$arResult["ID"]?>"></div>
	<script>
		var arFiles = [
			'<?=$this->__component->__path;?>/wmvplayer/silverlight.js',
			'<?=$this->__component->__path;?>/wmvplayer/wmvplayer.js'
		];
		<?if ($arResult["USE_JS_PLAYLIST"]):?>
		var JSMESS = {
			ClickToPLay : "<?= GetMessage('JS_CLICKTOPLAY')?>",
			Link : "<?= GetMessage('JS_LINK')?>",
			PlayListError: "<?= GetMessage('JS_PLAYLISTERROR')?>"
		};
		BX.loadCSS('<?=$templateFolder;?>/wmvplaylist.css');
		arFiles.push('<?=$templateFolder;?>/wmvscript_playlist.js');
		<?else:?>
		arFiles.push('<?=$this->__component->__path;?>/wmvplayer/wmvscript.js');
		<?endif;?>

		BX.loadScript(arFiles, function(){setTimeout(function(){
			if (window.showWMVPlayer)
				window.showWMVPlayer("<?=$arResult["ID"]?>", <?=\CUtil::PhpToJSObject($arResult['WMV_CONFIG']);?>, <?=\CUtil::PhpToJSObject($arResult['PLAYLIST_CONFIG']);?>);
		}, 100);});

	</script><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>

<?elseif($arResult["PLAYER_TYPE"] == "videojs"): // Attach video.js?>
	<?
	if($arResult['AUDIO_FILE'] === true)
	{
		$tag_name = 'audio';
	}
	else
	{
		$tag_name = 'video';
	}
	?>
	<<?=$tag_name;?> id="<?=$arResult["ID"];?>" class="video-js <?=$arResult['SKIN_NAME'];?> vjs-big-play-centered" width="<?=$arParams["WIDTH"];?>" height="<?=$arParams["HEIGHT"];?>"<?
	if($arParams["MUTE"] === "Y")
	{
		echo " muted";
	}
	/*if ($arParams["SHOW_CONTROLS"] != "N")
		echo " controls";*/
	?>>

	<?if($arParams["USE_PLAYLIST"] != 'Y' && !$arResult['YOUTUBE'])
	{?>
		<source src="<?=$arResult['PATH'];?>" type="<?=$arResult['FILE_TYPE'];?>">
	<?}?>
	</<?=$tag_name;?>><noscript><?=GetMessage('ENABLE_JAVASCRIPT')?></noscript>
	<script>
		BX.ready(function()
		{
			<?='BX.message('.\CUtil::PhpToJSObject(\Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__)).');'?>
			var init_player_<?=$arResult["ID"];?> = function()
			{
				// delete previous object to allow reinitializing
				if(videojs.players['<?=$arResult["ID"];?>'])
				{
					delete videojs.players['<?=$arResult["ID"];?>'];
				}
				player_<?=$arResult["ID"];?> = videojs("<?=$arResult["ID"];?>", <?=\CUtil::PhpToJSObject($arResult["VIDEOJS_PARAMS"]);?>);
				<?if($arResult['FLASH'])
				{?>
					player_<?=$arResult["ID"];?>.setTimeout(function()
					{
						if(!this.hasStarted())
						{
							this.error(BX.message('PLAYER_FLASH_REQUIRED'));
						}
					}, 10000);
				<?}?>
				player_<?=$arResult["ID"];?>.ready(BX.proxy(function()
				{
					this.volume(<?=floatval($arResult["VOLUME"]);?>);
					<?if($arParams['MUTE'] === 'Y')
					{?>
					this.muted(true);
					<?}?>
					var volume_set = false;
					this.one('play', BX.proxy(function()
					{
						<?if($arParams['PLAYBACK_RATE'] != 1)
						{?>
						this.playbackRate(<?=$arParams['PLAYBACK_RATE'];?>);
						<?}?>
						<?if(isset($arResult["VOLUME"]))
						{?>
						if(!volume_set)
						{
							this.volume(<?=floatval($arResult["VOLUME"]);?>);
							<?if($arParams['MUTE'] === 'Y')
							{?>
							this.muted(true);
							<?}?>
							volume_set = true;
						}
						<?}?>
					}, player_<?=$arResult["ID"];?>));
					<?if($arParams["USE_PLAYLIST"] == 'Y' && count($arResult['TRACKS']) > 1)
					{?>
						this.playlist(<?=\CUtil::PhpToJSObject($arResult["VIDEOJS_PLAYLIST_PARAMS"]);?>);
					<?}
					else
					{
					if($arParams['START_TIME'] > 0)
					{?>
						this.on('loadedmetadata', function()
						{
							this.currentTime(<?=intval($arParams['START_TIME']);?>);
							var spinner = BX.findChild(BX("<?=$arResult["ID"];?>"),
								{
									"class" : "vjs-loading-spinner"
								},
								false
							);
							if(spinner != undefined)
							{
								spinner.remove();
							}
						});
					<?}
					}
					if($arResult['WMV'])
					{?>
						this.wmvConfig = <?=\CUtil::PhpToJSObject($arResult["WMV_CONFIG"]);?>;
					<?}
					?>
				}, player_<?=$arResult["ID"];?>));
			}
			if(typeof videojs == 'undefined')
			{
				window.videojs_player_timout = true;
				<?if(!empty($arResult['CSS_FILES']))
				{?>
				BX.loadCSS(<?=\CUtil::PhpToJSObject($arResult['CSS_FILES']);?>);
				<?}
				if(!empty($arResult['JS_FILES']))
				{?>
				BX.loadScript(<?=\CUtil::PhpToJSObject($arResult['JS_FILES']);?>, function()
				{
					setTimeout(function()
					{
						init_player_<?=$arResult["ID"];?>()
					}, 100);
				});
				<?}?>
			}
			else
			{
				if(window.videojs_player_timout === true)
				{
					setTimeout(function() {
						init_player_<?=$arResult["ID"];?>();
					}, 100);
				}
				else
				{
					init_player_<?=$arResult["ID"];?>();
				}
			}
		});
	</script>
<?endif;?>