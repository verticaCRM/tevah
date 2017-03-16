/*********************************************************************************
 * Copyright (C) 2011-2014 X2Engine Inc. All Rights Reserved.
 *
 * X2Engine Inc.
 * P.O. Box 66752
 * Scotts Valley, California 95067 USA
 *
 * Company website: http://www.x2engine.com
 * Community and support website: http://www.x2community.com
 *
 * X2Engine Inc. grants you a perpetual, non-exclusive, non-transferable license
 * to install and use this Software for your internal business purposes.
 * You shall not modify, distribute, license or sublicense the Software.
 * Title, ownership, and all intellectual property rights in the Software belong
 * exclusively to X2Engine.
 *
 * THIS SOFTWARE IS PROVIDED "AS IS" AND WITHOUT WARRANTIES OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, INCLUDING WITHOUT LIMITATION THE IMPLIED WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE, TITLE, AND NON-INFRINGEMENT.
 ********************************************************************************/

/**
 * Extends functionality of jQuery UI sortable for the purposes of the workflow drag and drop UI.
 */


$.widget ("x2.workflowDragAndDropSortable", $.ui.sortable, {
    /**
     * Overrides parent method
     */
	_mouseStart: function(event, overrideHandle, noActivation) {
        // keep track of the original sort list  
        this.originalContainer = this;

        // determine whether dragged list item is the last item in the list
        if ($(this.originalContainer.element).children ().last ().get (0) === this.currentItem.get (0))
            this.isLastItem = true;
        else
            this.isLastItem = false;
        this._super (event, overrideHandle, noActivation);
    },
    /**
     * Overrides parent method so that sort lists have the following behavior:
     *  -within the original list, the list item remains in its original location
     *  -within other lists, the list item will always be prepended to the list, maintaining 
     *   lastUpdated ordering
     */
	_rearrange: function(event, i, a, hardRefresh) {
        if (this.currentContainer !== this.originalContainer || this.isLastItem) {

            if (this.isLastItem && this.currentContainer === this.originalContainer) {
                // item is last item in list, append to list

                originalRearrange.call (this, event, null, this.currentContainer.element, hardRefresh, true);
            } else { 
                // item is being dropped into another container, prepend it to that container's list

                originalRearrange.call (this, event, null, this.currentContainer.element, hardRefresh);
            }
        } else if (this.currentItem.next () || this.currentItem.prev ()) {
            // item is being dropped into original container, return it to its original position

            var sibling = this.currentItem.next () || this.currentItem.prev ();
            //console.log ('sibling = ');
            //console.log (sibling);

            originalRearrange.call (this, event, {item: [$(sibling).get (0)]}, null, hardRefresh);
        } 

        /*!
         * This function is a modified version of a base jQuery UI function
         * jQuery UI Sortable @VERSION
         * http://jqueryui.com
         *
         * Copyright 2012 jQuery Foundation and other contributors
         * Released under the MIT license.
         * http://jquery.org/license
         *
         * http://api.jqueryui.com/sortable/
         */
        function originalRearrange (event, i, a, hardRefresh, append) {
            //console.log (i);
            /* x2modstart */ 
            // modified so that place holder is prepended, instead of appended
            var append = typeof append === 'undefined' ? false : append; 
            if (append)
                a ? $(a[0]).append($(this.placeholder[0])) : i.item[0].parentNode.insertBefore(this.placeholder[0], (this.direction == 'down' ? i.item[0] : i.item[0].nextSibling));
            else 
                a ? $(a[0]).prepend($(this.placeholder[0])) : i.item[0].parentNode.insertBefore(this.placeholder[0], (this.direction == 'down' ? i.item[0] : i.item[0].nextSibling));

            /* x2modend */ 
            //Various things done here to improve the performance:
            // 1. we create a setTimeout, that calls refreshPositions
            // 2. on the instance, we have a counter variable, that get's higher after every append
            // 3. on the local scope, we copy the counter variable, and check in the timeout, if it's still the same
            // 4. this lets only the last addition to the timeout stack through
            this.counter = this.counter ? ++this.counter : 1;
            var counter = this.counter;

            this._delay(function() {
                if(counter == this.counter) this.refreshPositions(!hardRefresh); //Precompute after each DOM insertion, NOT on mousemove
            });
        }
    }
});



