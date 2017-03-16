<?php
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

class EmailInboxesPagination extends CPagination {

    /**
     * @var CDataProvider $dataProvider
     */
    public $dataProvider; 

    /**
     * @var int $messageCount
     */
    public $messageCount; 

    public function getPageCount () {
        return (int) (
            ($this->messageCount + EmailInboxes::OVERVIEW_PAGE_SIZE - 1) / 
            EmailInboxes::OVERVIEW_PAGE_SIZE);
    }

    public function addLastUidParam (&$params) {
        $currPage = $this->dataProvider->getData ();
        if (count ($currPage)) {
            $lastItem = array_pop ($currPage);
            $params['lastUid'] = $lastItem->uid;
        }
    }

    public function addFirstUidParam (&$params) {
        $currPage = $this->dataProvider->getData ();
        if (count ($currPage)) {
            $lastItem = array_shift ($currPage);
            $params['lastUid'] = $lastItem->uid;
        }
    }
    
    /**
     * This method is Copyright (c) 2008-2014 by Yii Software LLC
     * http://www.yiiframework.com/license/  
     */
    public function createPageUrl ($controller, $page/* x2modstart */, $next=false/* x2modend */) {
        $params=$this->params===null ? $_GET : $this->params;
        if($page>0) // page 0 is the default
            $params[$this->pageVar]=$page+1;
        else
            unset($params[$this->pageVar]);
        /* x2modstart */     
        if ($next)
            $this->addLastUidParam ($params);
        else
            $this->addFirstUidParam ($params);
        /* x2modend */ 
        return $controller->createUrl($this->route,$params);
    }

}

?>
