<div class="content-box">
      <!-- Start Content Box -->
      <!-- 用户界面展示当天所有订餐 -->
      <div class="content-box-header">
        <br>
        <h3 align="center" style="font-weight:bold;"><?php if($date):?><?php echo $date;?><?php else:?>今日<?php endif;?> 订单信息</h3>
        <br>
        <div class="clear"></div>
      </div>
      <!-- End .content-box-header -->
      <h3></h3>
      <H3>订单详细记录</H3>
      <div class="content-box-content">
        <div class="tab-content default-tab" id="tab1">
          <table width=800px>
            <thead>
              <tr style="font-weight:bold;">
                <td>餐类</td>
                <td>用户名</td>
                <td>订单状态</td>
                <td>订餐内容</td>
                <td>总价</td>
                <td>下单时间</td>
              </tr>
            </thead>
            <tfoot>
              <tr>
                <td colspan="7">
                  <div class="clear"></div>
                </td>
              </tr>
            </tfoot>
            <tbody>
               <?php if(isset($data)):?>
               		<?php foreach ($data AS $k => $v):?>
		              <tr>
		                <td><?php echo $v['shop_name'];?></td>
		                <td><?php echo $v['user_name'];?></td>
		                <td style="cursor:pointer;color:<?php echo $v['status_color'];?>"><?php echo $v['status_text'];?></td>
		                <td>
		                	
               		        <?php foreach($v['product_info'] AS $_k => $_v):?>
							<p><?php echo $_v['Name'];?> : <?php echo $_v['Price'];?>Ks x <?php echo $_v['Count'];?>------<?php echo $_v['smallTotal'];?>Ks</p>		                  
							<?php endforeach;?>
							----------------------------------------------------------
		                </td>
		                 <td>$ <?php echo $v['total_price'];?></td>
		                 <td><?php echo $v['create_time'];?></td>
		              </tr>
              		<?php endforeach;?>
              <?php endif;?>
            </tbody>
          </table>
        </div>
      </div>
      
       <H3>明细统计如下：</H3>
      
      <div class="content-box-content">
      	<table width=800px>
      		<thead>
              <tr style="font-weight:bold;">
                <td >餐类</td>
                <td>地点</td>
                <td>订餐清单</td>
              </tr>
            </thead>
            <tbody>
      		<?php foreach($orderViewList AS $k => $v):?>
      		<tr>
      			<td><?php echo $v['foodClass'];?><br>
      			    <?php echo '(共'.$v['totalCount'].'份)';?>
      			</td>
      			<td>
      				<?php foreach ($v['locations'] AS $_k => $_v):?>
      				<p>
      				
      				<?php echo $_v['locationName'].' 共  '.$_v['count'].' 份';?></p>
      				<?php endforeach;?>
				</td>
				<td>      				
      				<?php foreach ($v['locations'] AS $_k => $_v):?>
      				<?php foreach ($_v['orderItems'] AS $order_k => $order_v):?>
      				----&nbsp;&nbsp;&nbsp;&nbsp; <?php echo $order_v['userName'].' x '.$order_v['count'];?> 份<br>
      				
      				<?php endforeach;?>
				
      				<?php endforeach;?>
      				********************以上********************
      			</td>
      		</tr>
      		<?php endforeach;?>
      		</tbody>
      		<tfoot>
      			<tr>
      				<td>总计：</td>
	      			<td>共<?php echo $foodTotalCount;?> 份</td>
      			</tr>
      		</tfoot>
      	</table>
      </div>
</div>