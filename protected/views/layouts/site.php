<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head id="phead"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<title>MICT订餐网</title>
<?php 
/*加载css*/
foreach(array('base','common','main','login','home','order','food') AS $k => $v)
{
	Yii::app()->clientScript->registerCssFile(Yii::app()->baseUrl . '/assets/css/front/' .$v. '.css');
}

/*加载js*/

foreach(array('jquery-1.7.1.min','jquery.cookie','jquery.json-2.4.min') AS $k => $v)
{
	Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/assets/js/' .$v. '.js');
}
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/assets/js/front/cart.js');
Yii::app()->clientScript->registerScriptFile(Yii::app()->baseUrl . '/assets/js/front/jquery.wookmark.js');


?>
</head>
<body>
<div id="top">
    <div id="top_inner">
        <a class="logo" href="<?php echo Yii::app()->createUrl('site/index');?>">
            <img src="<?php echo Yii::app()->baseUrl;?>/assets/images/front/tranlogo1.png">
        </a>
        <ul class="top_NewNava">
            <li class="active"><a href="<?php echo Yii::app()->createUrl('site')?>">订餐好难啊~</a></li>
            <li><a href="<?php echo Yii::app()->createUrl('site/index') ?>"> <?php echo date('Y-m-d H:i:s');?> </a></li>
            <!-- <li><a href="#">我要吐槽</a></li> -->
        </ul>
        <div id="top_quickmenu" class="quick_menu">
            <dl>
                
                <!-- 
                <dd>
                    <a id="top_orderViewLink" class="MyOrderLink" href="<?php echo Yii::app()->createUrl('site/orderView');?>">今天订单清单</a>
                </dd>
                -->
                
                <dd>
                	<?php if(isset(Yii::app()->user->member_userinfo)):?>
                    <a id="top_MyOrderLink" class="MyOrderLink" href="<?php echo Yii::app()->createUrl('site/membercenter');?>">用户中心</a>
                    <?php endif;?>
                </dd>
                <dd class="all_mess">
                    <span id="top_notification" class="notification"><b class="message_prompt">
                        &nbsp;</b> <span id="top_num" class="num" style="display:none;"></span>
                        <div id="top_MessageContent" class="layer_top_account layer_top_Message br4 shadow " style="display: none;">
                            <span class="arrow"></span>
                            <ul id="top_MessageBox" class="MessageBox"><li class="No_Message_Type"><div>今日没有消息</div></li><li class="btn clearfix noPointer"><input type="submit" class="setallmessage" value="知道了"></li></ul>
                        </div>
                    </span> 
                </dd>
                <dd id="top_top_user" class="Newaccount">
                    <div class="login_regist">
                        <?php if(isset(Yii::app()->user->member_userinfo)):?>
                        <a href="<?php echo Yii::app()->createUrl('site/membercenter');?>" class="log_btn"><?php echo Yii::app()->user->member_userinfo['username'];?></a> <span>|</span>
                        <a href="<?php echo Yii::app()->createUrl('site/logout');?>" class="log_btn regist">退出</a>
                        <?php else: ?>
                        <a href="<?php echo Yii::app()->createUrl('site/login');?>" class="log_btn">登录</a> <span>.</span>
                        <?php endif;?>
                    </div>
                </dd>
            </dl>
        </div>
        <!--end of quickmenu-->
    </div>
</div>


<div id="wrap">
	<div id="container" class="clearfix">
	           <?php echo $content;?>
	</div>
</div>

<div id="footer">
    <hr/>
    <p>Copyright © 2015-2016 MICT订餐网 All Rights Reserved 缅ICP备997号 | 仰光网安备997号</p>
</div>

<div style="text-align: center;">
<a href="http://www.google.com" target="_blank"></a>
</div>
</body>
</html>