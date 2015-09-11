<?php
error_reporting(E_ALL & ~E_NOTICE);
class IndexController extends Controller
{
	//后台首页
	public function actionIndex()
	{
		$data['home_pic'] = Yii::app()->baseUrl . '/assets/images/' . Yii::app()->params['homeIndexPic'];
		$this->render('index',$data);
	}
}