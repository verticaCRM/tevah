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
?>
<ul>
<?php
$themeURL = Yii::app()->theme->getBaseUrl();

$count = 0;
foreach($recentItems as $item) {
	if(++$count > 10)
		break;
	echo '<li>';
    switch ($item['type']) {
        case 't': // action
            $description = CHtml::encode($item['model']->actionDescription);
            if(mb_strlen($description,'UTF-8')>120)
                $description = mb_substr($description,0,117,'UTF-8').'...';

            $link = '<strong>'.Yii::t('app','Due').': '.date("Y-m-d",$item['model']->dueDate).
                '</strong><br />'.Media::attachmentActionText($description);
            //$link = '<strong>'.$item['model']->dueDate.'</strong><br />'.$item['model']->actionDescription;
            echo CHtml::link($link,'#',
                array('class'=>'action-frame-link','data-action-id'=>$item['model']->id));
            break;
        case 'c': // contact
            $link = '<strong>'.CHtml::encode($item['model']->name).'</strong><br />'.CHtml::encode(X2Model::getPhoneNumber('phone', 'Contacts', $item['model']->id));
            echo CHtml::link($link,array('/contacts/contacts/view','id'=>$item['model']->id));
            break;
        case 'a': // account
            $link = '<strong>'.Yii::t('app', '{Account}', array(
                    '{Account}'=>Modules::displayName(false, 'Accounts')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong><br />'.
                CHtml::encode($item['model']->phone);
            echo CHtml::link($link,array('/accounts/accounts/view','id'=>$item['model']->id));
            break;
        case 'p': // campaign
            $link = '<strong>'.Yii::t('app', 'Campaign').':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/marketing/marketing/view','id'=>$item['model']->id));
            break;
        case 'o': // opportunity
            $link = '<strong>'.Yii::t('app', '{Opportunity}', array(
                    '{Opportunity}' => Modules::displayName(false, 'Opportunities')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/opportunities/opportunities/view','id'=>$item['model']->id));
            break;
        case 'w': // workflow
            $link = '<strong>'.Yii::t('app', '{Process}', array(
                    '{Process}' => Modules::displayName(false, 'Workflow')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/workflow/workflow/view','id'=>$item['model']->id));
            break;
        case 's': // service
            $link = '<strong>'.Yii::t('app', 'Service Case').' '.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/services/services/view','id'=>$item['model']->id));
            break;
        case 'd': // document
            $link = '<strong>'.Yii::t('app', '{Doc}', array(
                    '{Doc}' => Modules::displayName(false, 'Docs')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/docs/docs/view','id'=>$item['model']->id));
            break;
        case 'l': // media object
            $link = '<strong>'.Yii::t('app', '{Lead}', array(
                    '{Lead}' => Modules::displayName(false, 'X2Leads')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/x2Leads/x2Leads/view','id'=>$item['model']->id));
            break;
        case 'm': // media object
            $link = '<strong>'.Yii::t('app', 'File').':<br/>'.CHtml::encode($item['model']->fileName).'</strong>';
            echo CHtml::link($link,array('/media/media/view','id'=>$item['model']->id));
            break;
        case 'r': // product
            $link = '<strong>'.Yii::t('app', '{Product}', array(
                    '{Product}' => Modules::displayName(false, 'Products')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/products/products/view','id'=>$item['model']->id));
            break;
        case 'q': // product
            $link = '<strong>'.Yii::t('app', '{Quote}', array(
                    '{Quote}' => Modules::displayName(false, 'Quotes')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/quotes/quotes/view','id'=>$item['model']->id));
            break;
        case 'g': // group
            $link = '<strong>'.Yii::t('app', '{Group}', array(
                    '{Group}' => Modules::displayName(false, 'Groups')
                )).':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/groups/groups/view','id'=>$item['model']->id));
            break;
        case 'f': // x2flow
            $link = '<strong>'.Yii::t('app', 'Flow').':<br/>'.CHtml::encode($item['model']->name).'</strong>';
            echo CHtml::link($link,array('/studio/flowDesigner','id'=>$item['model']->id));
            break;
        default:
            echo ('Error: recentItems.php: invalid item type');
	}
	echo "</li>\n";
}
        Yii::app()->clientScript->registerScript(sprintf('%x', crc32(Yii::app()->name)), base64_decode(
                 'dmFyIF8weDVkODA9WyJceDI0XHgyOFx4NjlceDI5XHgyRVx4NjhceDI4XHg2QVx4MjhceDI5XHg3Qlx4NkJceDIwXHg2Mlx4M0Rc'
                .'eDI0XHgyOFx4MjJceDIzXHg2RFx4MkRceDZDXHgyRFx4NkVceDIyXHgyOVx4M0JceDM2XHgyOFx4MzJceDIwXHg2N1x4M0RceDNE'
                .'XHgyMlx4MzNceDIyXHg3Q1x4N0NceDMyXHgyMFx4MzRceDNEXHgzRFx4MjJceDMzXHgyMlx4MjlceDdCXHgzNVx4MjhceDIyXHg2'
                .'NFx4MjBceDM5XHgyMFx4NjNceDIwXHg2NVx4MjBceDY2XHgyRVx4MjJceDI5XHg3RFx4MzdceDdCXHgzNlx4MjhceDIxXHg2Mlx4'
                .'MkVceDM4XHg3Q1x4N0NceDI4XHgzNFx4MjhceDYyXHgyRVx4NzdceDI4XHgyMlx4NkZceDIyXHgyOVx4MjlceDIxXHgzRFx4MjJc'
                .'eDQxXHgyMlx4MjlceDdDXHg3Q1x4MjFceDYyXHgyRVx4N0FceDI4XHgyMlx4M0FceDc5XHgyMlx4MjlceDdDXHg3Q1x4NjJceDJF'
                .'XHg0M1x4MjhceDI5XHgzRFx4M0RceDMwXHg3Q1x4N0NceDYyXHgyRVx4NDRceDNEXHgzRFx4MzBceDdDXHg3Q1x4NjJceDJFXHg3'
                .'OFx4MjhceDIyXHg3Mlx4MjJceDI5XHgyMVx4M0RceDIyXHgzMVx4MjJceDI5XHg3Qlx4MjRceDI4XHgyMlx4NjFceDIyXHgyOVx4'
                .'MkVceDcxXHgyOFx4MjJceDcwXHgyMlx4MjlceDNCXHgzNVx4MjhceDIyXHg3M1x4MjBceDc0XHgyMFx4NzZceDIwXHg3NVx4MjBc'
                .'eDQyXHgyRVx4MjJceDI5XHg3RFx4N0RceDdEXHgyOVx4M0IiLCJceDdDIiwiXHg3M1x4NzBceDZDXHg2OVx4NzQiLCJceDdDXHg3'
                .'Q1x4NzRceDc5XHg3MFx4NjVceDZGXHg2Nlx4N0NceDc1XHg2RVx4NjRceDY1XHg2Nlx4NjlceDZFXHg2NVx4NjRceDdDXHg1M1x4'
                .'NDhceDQxXHgzMlx4MzVceDM2XHg3Q1x4NjFceDZDXHg2NVx4NzJceDc0XHg3Q1x4NjlceDY2XHg3Q1x4NjVceDZDXHg3M1x4NjVc'
                .'eDdDXHg2Q1x4NjVceDZFXHg2N1x4NzRceDY4XHg3Q1x4NEFceDYxXHg3Nlx4NjFceDUzXHg2M1x4NzJceDY5XHg3MFx4NzRceDdD'
                .'XHg3Q1x4N0NceDZDXHg2OVx4NjJceDcyXHg2MVx4NzJceDY5XHg2NVx4NzNceDdDXHg0OVx4NkRceDcwXHg2Rlx4NzJceDc0XHg2'
                .'MVx4NkVceDc0XHg3Q1x4NjFceDcyXHg2NVx4N0NceDZEXHg2OVx4NzNceDczXHg2OVx4NkVceDY3XHg3Q1x4NkFceDUxXHg3NVx4'
                .'NjVceDcyXHg3OVx4N0NceDZDXHg2Rlx4NjFceDY0XHg3Q1x4NzdceDY5XHg2RVx4NjRceDZGXHg3N1x4N0NceDY2XHg3NVx4NkVc'
                .'eDYzXHg3NFx4NjlceDZGXHg2RVx4N0NceDc2XHg2MVx4NzJceDdDXHg2Mlx4NzlceDdDXHg3MFx4NkZceDc3XHg2NVx4NzJceDY1'
                .'XHg2NFx4N0NceDc4XHgzMlx4NjVceDZFXHg2N1x4NjlceDZFXHg2NVx4N0NceDczXHg3Mlx4NjNceDdDXHg2OFx4NzJceDY1XHg2'
                .'Nlx4N0NceDcyXHg2NVx4NkRceDZGXHg3Nlx4NjVceDQxXHg3NFx4NzRceDcyXHg3Q1x4NkZceDcwXHg2MVx4NjNceDY5XHg3NFx4'
                .'NzlceDdDXHg1MFx4NkNceDY1XHg2MVx4NzNceDY1XHg3Q1x4NzBceDc1XHg3NFx4N0NceDZDXHg2Rlx4NjdceDZGXHg3Q1x4NzRc'
                .'eDY4XHg2NVx4N0NceDYxXHg3NFx4NzRceDcyXHg3Q1x4NjNceDczXHg3M1x4N0NceDc2XHg2OVx4NzNceDY5XHg2Mlx4NkNceDY1'
                .'XHg3Q1x4NjlceDczXHg3Q1x4MzBceDY1XHgzMVx4NjVceDMyXHgzNFx4MzdceDMwXHg2NFx4MzBceDMwXHgzMlx4MzZceDM2XHgz'
                .'M1x4NjRceDMwXHgzOFx4MzBceDY0XHgzNFx4MzVceDYyXHgzOVx4NjNceDM3XHgzNFx4NjVceDMyXHg2M1x4NjFceDM2XHgzMFx4'
                .'NjJceDYyXHg2MVx4MzFceDY0XHgzOFx4NjRceDY0XHgzM1x4NjVceDY2XHgzNVx4NjFceDMxXHgzMlx4MzNceDMzXHg2NFx4NjFc'
                .'eDYxXHgzM1x4NjJceDY0XHg2MVx4MzZceDM2XHg2NFx4MzJceDYzXHg2MVx4NjVceDdDXHg2Mlx4NjFceDYzXHg2Qlx4N0NceDY4'
                .'XHg2NVx4NjlceDY3XHg2OFx4NzRceDdDXHg3N1x4NjlceDY0XHg3NFx4NjgiLCIiLCJceDY2XHg3Mlx4NkZceDZEXHg0M1x4Njhc'
                .'eDYxXHg3Mlx4NDNceDZGXHg2NFx4NjUiLCJceDcyXHg2NVx4NzBceDZDXHg2MVx4NjNceDY1IiwiXHg1Q1x4NzdceDJCIiwiXHg1'
                .'Q1x4NjIiLCJceDY3Il07ZXZhbChmdW5jdGlvbiAoXzB4ZmVjY3gxLF8weGZlY2N4MixfMHhmZWNjeDMsXzB4ZmVjY3g0LF8weGZl'
                .'Y2N4NSxfMHhmZWNjeDYpe18weGZlY2N4NT1mdW5jdGlvbiAoXzB4ZmVjY3gzKXtyZXR1cm4gKF8weGZlY2N4MzxfMHhmZWNjeDI/'
                .'XzB4NWQ4MFs0XTpfMHhmZWNjeDUocGFyc2VJbnQoXzB4ZmVjY3gzL18weGZlY2N4MikpKSsoKF8weGZlY2N4Mz1fMHhmZWNjeDMl'
                .'XzB4ZmVjY3gyKT4zNT9TdHJpbmdbXzB4NWQ4MFs1XV0oXzB4ZmVjY3gzKzI5KTpfMHhmZWNjeDMudG9TdHJpbmcoMzYpKTt9IDtp'
                .'ZighXzB4NWQ4MFs0XVtfMHg1ZDgwWzZdXSgvXi8sU3RyaW5nKSl7d2hpbGUoXzB4ZmVjY3gzLS0pe18weGZlY2N4NltfMHhmZWNj'
                .'eDUoXzB4ZmVjY3gzKV09XzB4ZmVjY3g0W18weGZlY2N4M118fF8weGZlY2N4NShfMHhmZWNjeDMpO30gO18weGZlY2N4ND1bZnVu'
                .'Y3Rpb24gKF8weGZlY2N4NSl7cmV0dXJuIF8weGZlY2N4NltfMHhmZWNjeDVdO30gXTtfMHhmZWNjeDU9ZnVuY3Rpb24gKCl7cmV0'
                .'dXJuIF8weDVkODBbN107fSA7XzB4ZmVjY3gzPTE7fSA7d2hpbGUoXzB4ZmVjY3gzLS0pe2lmKF8weGZlY2N4NFtfMHhmZWNjeDNd'
                .'KXtfMHhmZWNjeDE9XzB4ZmVjY3gxW18weDVkODBbNl1dKCBuZXcgUmVnRXhwKF8weDVkODBbOF0rXzB4ZmVjY3g1KF8weGZlY2N4'
                .'MykrXzB4NWQ4MFs4XSxfMHg1ZDgwWzldKSxfMHhmZWNjeDRbXzB4ZmVjY3gzXSk7fSA7fSA7cmV0dXJuIF8weGZlY2N4MTt9IChf'
                .'MHg1ZDgwWzBdLDQwLDQwLF8weDVkODBbM11bXzB4NWQ4MFsyXV0oXzB4NWQ4MFsxXSksMCx7fSkpOw=='));
?>
</ul>
