;(function() {
	'use strict';

	BX.namespace('BX.Grid');


	/**
	 * BX.Grid.PinHeader
	 * @param {BX.Main.grid} parent
	 * @constructor
	 */
	BX.Grid.PinHeader = function(parent)
	{
		this.parent = null;
		this.table = null;
		this.header = null;
		this.container = null;
		this.init(parent);
	};

	BX.Grid.PinHeader.prototype = {
		init: function(parent)
		{
			this.parent = parent;
			this.rect = BX.pos(this.parent.getHead());
			this.create();

			document.addEventListener('scroll', BX.proxy(this._onScroll, this), BX.Grid.Utils.listenerParams({passive: true}));
			document.addEventListener('resize', BX.proxy(this._onResize, this), BX.Grid.Utils.listenerParams({passive: true}));
			BX.addCustomEvent('Grid::updated', BX.proxy(this._onGridUpdate, this));
			BX.bind(window, 'resize', BX.proxy(this._onGridUpdate, this));
		},

		_onGridUpdate: function()
		{
			var isPinned = this.isPinned();

			BX.remove(this.getContainer());
			this.create();

			isPinned && this.pin();

			this.table = null;

			BX.onCustomEvent(window, 'Grid::headerUpdated', []);
		},

		create: function()
		{
			var cells = BX.Grid.Utils.getByTag(this.parent.getHead(), 'th');
			var cellsLength = cells.length-1;
			var cloneThead = BX.clone(this.parent.getHead());
			var cloneCells = BX.Grid.Utils.getByTag(cloneThead, 'th');

			cells.forEach(function(cell, index) {
				var width = BX.width(cell);
				cloneCells[index].firstElementChild && (cloneCells[index].firstElementChild.style.width = width + 'px');

				if (cellsLength !== index)
				{
					cloneCells[index].style.width = width + 'px';
				}
				else
				{
					if (this.parent.getRows().getCountDisplayed() > 0)
					{
						cloneCells[index].style.width = '100%';
					}
				}
			}, this);

			this.container = BX.decl({
				block: 'main-grid-fixed-bar',
				mix: 'main-grid-fixed-top',
				attrs: {
					style: 'width: ' + BX.width(this.parent.getContainer()) + 'px'
				},
				content: {
					block: 'main-grid-table',
					tag: 'table',
					content: cloneThead
				}
			});

			this.container.hidden = true;

			this.parent.getWrapper().appendChild(this.container);
		},

		getContainer: function()
		{
			return this.container;
		},

		getFixedTable: function()
		{
			return this.table || (this.table = BX.Grid.Utils.getByTag(this.getContainer(), 'table', true));
		},

		pin: function()
		{
			!!this.getContainer() && (this.getContainer().hidden = false);
			BX.onCustomEvent(window, 'Grid::headerPinned', []);
		},

		unpin: function()
		{
			!!this.getContainer() && (this.getContainer().hidden = true);
			BX.onCustomEvent(window, 'Grid::headerUnpinned', []);
		},

		isPinned: function()
		{
			return !this.getContainer().hidden;
		},

		_onScroll: function()
		{
			if (this.rect.top <= window.scrollY)
			{
				!this.isPinned() && this.pin();
			}
			else
			{
				this.isPinned() && this.unpin();
			}
		},

		_onResize: function()
		{
			this.rect = BX.pos(this.parent.getHead());
		}
	}
})();