<?php 
if(isset($data) && $data)
{
	$a = 'update';
	$op_text = '更新';
	$_action = Yii::app()->createUrl('shops/update');
}
else 
{
	$a = 'create';
	$op_text = '创建';
	$_action = Yii::app()->createUrl('shops/create');
}
?>
<div class="content-box">
      <!-- Start Content Box -->
      <div class="content-box-header">
        <h3><?php echo $op_text;?>餐类</h3>
        <ul class="content-box-tabs">
          <li><a href="#tab1" class="default-tab">表单</a></li>
        </ul>
        <div class="clear"></div>
      </div>
	  <div class="content-box-content">
		<div class="tab-content default-tab" id="tab1">
          <form action="<?php echo $_action;?>" method="post" enctype="multipart/form-data">
            <fieldset>
            <p>
              <label>名称</label>
              <input class="text-input small-input" type="text"  name="Shops[name]" value="<?php echo CHtml::encode($data['name']); ?>"/>
            </p>
            
            <p>
              <label>logo</label>
              <input class="text-input small-input" type="file"  name="logo" />
            </p>
            
            <p>
             	<img src="<?php echo CHtml::encode($data['logo']);?>" width="160" hieght="120" />
            </p>
            
            <p>
              <label>餐类说明</label>
              <input class="text-input small-input" type="text" name="Shops[address]" value="<?php echo CHtml::encode($data['address']); ?>" />
            </p>
            
            <p>
              <label>订餐起始时间(格式: hh:mm:ss 如:13:30:00)</label>
              <input class="text-input small-input" type="text" name="Shops[starttime]" value="<?php echo CHtml::encode($data['starttime']); ?>"/>
            </p>
            
            <p>
              <label>订餐结束时间(格式: hh:mm:ss 如:16:30:00)</label>
              <input class="text-input small-input" type="text" name="Shops[endtime]" value="<?php echo CHtml::encode($data['endtime']); ?>"/>
            </p>
            

            
            <p>
              <label>食品简介</label>
              <input class="text-input small-input" type="text" name="Shops[url]" value="<?php echo CHtml::encode($data['url']); ?>"/>
            </p>
            
            <p>
              <input type="hidden" name="id" value="<?php echo $data['id'];?>" />
              <input class="button" type="submit" value="<?php echo $op_text;?>" />
            </p>
            </fieldset>
            <div class="clear"></div>
            <!-- End .clear -->
          </form>
        </div>
     </div>
 </div>