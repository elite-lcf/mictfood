<?php

/**
 * This is the model class for table "{{shops}}".
 *
 * The followings are the available columns in table '{{shops}}':
 * @property integer $id
 * @property string $name
 * @property integer $district_id
 * @property string $address
 * @property integer $create_time
 * @property integer $update_time
 * @property string $logo
 * @property string $tel -> endtime
 * @property string $linkman -> starttime
 * @property integer $order_id
 *  Shop - 餐类
 *	菜单 - 地点
 *	我们为每一个Shop店都需要建立一个起始订购时间和结束订购时间
 *  考虑到最大可复用性，我们使用原定义linkman->starttime，tel->endtime
 *  格式统一为hh:mm:ss
 */
class Shops extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return '{{shops}}';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, address, create_time, update_time, starttime,endtime', 'required'),
			array('logo,order_id,status,url','safe'),
			array('id, name, district_id, address, create_time, update_time, logo, endtime, starttime, order_id ,url', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
			'image' 	=> array(self::HAS_ONE, 'Material', '', 'on' => 't.logo = image.id'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'name' => 'Name',
			'district_id' => 'District',
			'address' => 'Address',
			'create_time' => 'Create Time',
			'update_time' => 'Update Time',
			'logo' => 'Logo',
			'endtime' => 'Endtime',
			'starttime' => 'Starttime',
			'order_id' => 'Order',
			'url' => 'Url',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('name',$this->name,true);
		$criteria->compare('district_id',$this->district_id);
		$criteria->compare('address',$this->address,true);
		$criteria->compare('create_time',$this->create_time);
		$criteria->compare('update_time',$this->update_time);
		$criteria->compare('logo',$this->logo,true);
		$criteria->compare('endtime',$this->endtime,true);
		$criteria->compare('starttime',$this->starttime,true);
		$criteria->compare('order_id',$this->order_id);
		$criteria->compare('url',$this->url);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Shops the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
