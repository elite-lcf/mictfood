<div id="left">
                <!--在线餐厅开始-->
                <div id="onlineSup" class="sup_list">
                    <div class="sup_list_title">
                        <h2>在线订餐</h2>
                        <p class="si_filter">
                            <!--  <input id="showOnLine" class="fl" type="checkbox"><span class="fl">仅显示营业中</span>-->
                        </p>
                        <p class="step">
                            <span>交预付款</span><span>选中餐/晚餐</span><span>选地点</span><span>确认订单</span><span>行政MM统一收集</span>
                        </p>
                    </div>
                    <div class="sup_list_body shadow" id="hallListOnline">
                        <table cellpadding="0" cellspacing="0" id="SupplierListBody">       
                         <tbody>
                         	<?php 
                         		$_tr_num = count($shops)/4;
                         		for($i = 0;$i<$_tr_num;$i++):
                         	?>
                         		<tr>
                              		<?php for($j = 0;$j<4;$j++):
                              				$_index = $i * 4 + $j;
                                    		if($_index >= (count($shops)))
                                    		{
                                    			break;
                                    		}
                                    		//根据每个shop的starttime和endtime，判断是否在营业时间
                                    		$_shopid = $shops[$_index]['id'];
                                    		$isOnTime = Yii::app()->check_time->isShopOnTime($_shopid);
                              		?>	
                                    <td>
                                        <div class="si_block<?php if(!$isOnTime):?> si_closed <?php endif;?>" sid="0">
                                            <div class="si_logo">
                                            	<?php if($isOnTime):?>
                                                <a href="<?php echo Yii::app()->createUrl('site/lookmenu',array('shop_id' => $shops[$_index]['id']));?>">
                                                <?php else:?>
                                                <a href="javascript:alert('不好意思啦，已经打烊啦');">
                                                <?php endif;?>
                                                    <img src="<?php echo $shops[$_index]['logo'];?>"   width="43px" height="43px" style="display: inline;">
                                                </a>
                                            </div>
                                            <div class="si_info">
                                                <p class="si_name">
                                                	<?php if($isOnTime):?>
                                                    <a href="<?php echo Yii::app()->createUrl('site/lookmenu',array('shop_id' => $shops[$_index]['id']));?>">
                                                    <?php else:?>
                                                    <a href="javascript:alert('不好意思啦，已经打烊啦');">
                                                    <?php endif;?>
                                                	<?php echo $shops[$_index]['name'];?>
                                                    </a>
                                                </p>
                                                <?php if($isOnTime):?>
                                                <p class="si_rec star">推荐度：0星</p>
                                                <p class="si_com"><em>&nbsp;</em></p>
                                                <?php if ($shops[$_index]['url']):?>
                                                	<a href="<?php echo $shops[$_index]['url'];?>" target="_blank">商家网站</a>
                                                <?php endif;?>
                                                <?php else:?>
                                                <span class="rest"></span>
                                                <p class="rest">已打烊</p>
                                                <?php endif;?>
                                            </div>
                                        </div>
                                    </td>
                                    <?php endfor;?>
                               </tr> 
                               <?php endfor;?>                               
                        </tbody>
                        </table>
                    </div>
                </div>
                <!--在线餐厅结束-->
</div>

<div id="right">
	<div class="right_item shadow" id="siteNotice">
	    <h3>公告栏</h3>
	    <div class="ri_body">
	    <?php foreach ($announce AS $_k => $_v):?>
	    <p><?php echo $_v['content'];?></p>
	    <?php endforeach;?>
	    </div>
	</div>
	
	<div class="right_item shadow" id="quickLinks">
	    <h3>快速服务</h3>
	    <div class="ri_body" style="font-weight:bold;">
	   	<a href="<?php echo Yii::app()->createUrl('site/orderView') ?>">查询今天订餐列表 </a>
	    </div>
	</div>
	
	<div class="right_item shadow" id="customerService">
	    <h3>
	        客户服务</h3>
	    <div class="ri_body">
	        <p>行政MM: Kyat Kyat</p>
	        <p>技术支持: Huawei MS Team</p>
	    </div>
	</div>

</div>