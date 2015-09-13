<?php
error_reporting(E_ALL & ~E_NOTICE);
//前端页面控制器
class SiteController extends FormerController
{
	private $order;//购物车里面的订单数据
	//控制几个页面的访问
	public function filters()
	{
		return array(
			'checkLoginControl + lookmenu,confirmorder,orderok,membercenter,myorder,modifypassword,domodify,systemnotice,cancelorder,seeconsume',//检测是否登录
			'checkIsCartEmpty + lookcart,confirmorder',//检测购物车是否为空
			'checkReqiest + doregister,domodify,submitmessage,replymessage',//判断是不是ajax请求
			'checkIsOnTime +lookmenu,lookcart,confirmorder',//判断是否在订餐时间内
		    'checkAdmin +register',//判断是否有管理员权限
		);
	}
	
    //控制管理员是否登录
	public function filtercheckAdmin($filterChain)
	{
	    if(!isset(Yii::app()->user->admin_userinfo))
		{
			$this->redirect(Yii::app()->createUrl('index'));
		}
		$filterChain->run();
	}

	
	//控制会员是否登录
	public function filtercheckLoginControl($filterChain)
	{
		if(!isset(Yii::app()->user->member_userinfo))
		{
			$this->redirect(Yii::app()->createUrl('site/login'));
		}
		$filterChain->run();
	}
	
	//检测购物车是否为空
	public function filtercheckIsCartEmpty($filterChain)
	{
		$_product = Yii::app()->request->cookies['cart'];
		$order = array();
		if($_product)
		{
			$order = json_decode($_product->value,1);
			if($order['Items'])
			{
				foreach ($order['Items'] AS $k => $v)
				{
					$order['Items'][$k]['smallTotal'] = $v['Count'] * $v['Price'];
				}
			}
		}
		
		//如果购物车里面没有东西就报错
		if(!$order || !$order['Items'])
		{
			throw new CHttpException(404,Yii::t('yii','当前购物车没有美食'));
		}
		
		$this->order = $order;
		$filterChain->run();
	}
	
	//判断是不是ajax请求
	public function filtercheckReqiest($filterChain)
	{
		if(!Yii::app()->request->isAjaxRequest)
		{
			throw new CHttpException(404,Yii::t('yii','非法操作'));
		}
		
		if(!Yii::app()->request->isPostRequest)
		{
			throw new CHttpException(404,Yii::t('yii','非法操作'));
		}
		$filterChain->run();
	}
	
	public function filtercheckIsOnTime($filterChain)
	{
		$shop_id = Yii::app()->request->getParam('shop_id');
		
	    if(!Yii::app()->check_time->isShopOntime($shop_id))
		{
			throw new CHttpException(404,Yii::t('yii','不在当前餐类的订餐时间内！'));
		}
		$filterChain->run();
	}
	
	public function actions()
	{
		return array(
              'captcha' => array(
                    'class'		=>'CCaptchaAction',
                    'maxLength'	=> 4,       // 最多生成几个字符
                    'minLength'	=> 4,       // 最少生成几个字符
					'testLimit' => 999,
					//'fixedVerifyCode' => substr(md5(time()),11,4), //每次都刷新验证码
            ), 
         ); 
	}

	//前台首页
	public function actionIndex()
	{
		//取出商家的数据
		$model = Shops::model()->with('image')->findAll('t.status=:status',array(':status' => 2));
		$shopData = array();
		foreach($model AS $k => $v)
		{
			$shopData[$k] = $v->attributes;
			$shopData[$k]['logo'] = $shopData[$k]['logo']?Yii::app()->params['img_url'] . $v->image->filepath . $v->image->filename:'';
		}
		
		//取出公告数据
		$notice = Announcement::model()->findAll(array('order' => 'create_time DESC','condition' => 'status=:status','params'=>array(':status'=>2)));
		$notice = CJSON::decode(CJSON::encode($notice));
		
		//查询出会员账户余额小于10Ks的用户
		$members = Members::model()->findAll('balance < :balance',array(':balance' => 20));
		$members = CJSON::decode(CJSON::encode($members));

		//输出数据
		$output = array(
			'shops' 	=> $shopData,
			'announce' 	=> $notice,
			'members'	=> $members,
			'isOnTime'  => Yii::app()->check_time->isOnTime(),
		);		
		$this->render('index',$output);
	}
	
	//进入某个餐厅查看菜单
	public function actionLookMenu()
	{
		$shop_id = Yii::app()->request->getParam('shop_id');
		if(!isset($shop_id))
		{
			throw new CHttpException(404,Yii::t('yii','请选择就餐类别'));
		}
		
	    $resident_flag = Yii::app()->request->getParam('resident_flag');
		if(isset($resident_flag))
		{
			throw new CHttpException(404,Yii::t('yii','常驻员工每天每餐仅每次仅允许订购一份！！'));
		}
		
		//查询出改商店的一些详细信息
		$shopData = Shops::model()->findByPk($shop_id);
		if(!$shopData)
		{
			throw new CHttpException(404,Yii::t('yii','您选择的餐类不存在！'));
		}
		$shopData = CJSON::decode(CJSON::encode($shopData));
		
		//判断改商家有没有下市场
		if(intval($shopData['status']) != 2)
		{
		    throw new CHttpException(404,Yii::t('yii','您选择的餐类已经下市！'));
		}

		
		//根据订单历史纪录查询当天是否已经存在同样的中餐、晚餐订单记录
		//店铺->中餐、晚餐
		//菜单->地点
		//LCF
		$member_id = Yii::app()->user->member_userinfo['id'];
		$criteria = new CDbCriteria;
		$today = strtotime(date('Y-m-d'));
		$criteria->order = 't.create_time DESC';
		$criteria->select = '*';
		$criteria->condition = 'food_user_id=:food_user_id AND shop_id = :shop_id AND create_time > :create_time AND status < :status';
		$criteria->params = array(
		':food_user_id' => $member_id,
		':shop_id' => $shop_id,
		':create_time' => $today,
		':status' => 3
		);
		
		//查询结果
		$todayOrderCount=FoodOrder::model()->count($criteria);
		$memberInfo = Members::model()->find('id=:id',array(':id' => Yii::app()->user->member_userinfo['id']));
		
	    if($memberInfo->resident == 1 && $todayOrderCount >= 1)
		{
			throw new CHttpException(404,Yii::t('yii','你今天已经订过该餐了！！常驻员工每天每餐仅每次仅允许订购一份！！'));
		}
		
		
		
		
		//根据店铺id查询出该店铺的菜单
		$menuData = Menus::model()->with('food_sort','image','shops')->findAll(array('condition' => 't.shop_id=:shop_id AND t.status=:status','params' => array(':shop_id' => $shop_id,':status' => 2)));
		$data = array();
		foreach($menuData AS $k => $v)
		{
			$data[$k] = $v->attributes;
			$data[$k]['index_pic'] = $v->index_pic?Yii::app()->params['img_url'] . $v->image->filepath . $v->image->filename:'';
			$data[$k]['sort_name'] = $v->food_sort->name;
			$data[$k]['shop_name'] = $v->shops->name;
			$data[$k]['create_time'] = Yii::app()->format->formatDate($v->create_time);
			$data[$k]['status'] = Yii::app()->params['menu_status'][$v->status];
			$data[$k]['price'] = $v->price;
		}
		
		//获取该店的留言
		$criteria = new CDbCriteria();
		$criteria->order = 't.order_id DESC';
		$criteria->condition = 't.shop_id=:shop_id AND t.status=:status';
		$criteria->params = array(':shop_id' => $shop_id,':status' => 1);
		$count=Message::model()->count($criteria);
		//构建分页
		$pages = new CPagination($count);
 		$pages->pageSize = Yii::app()->params['pagesize'];
		$pages->applyLimit($criteria);
		$messageMode = Message::model()->with('members','shops','replys')->findAll($criteria);
		$message = array();
		foreach($messageMode AS $k => $v)
		{
			$message[$k] = $v->attributes;
			$message[$k]['shop_name'] = $v->shops->name;
			$message[$k]['user_name'] = $v->members->name;
			$message[$k]['create_time'] = date('Y-m-d H:i:s',$v->create_time);
			$message[$k]['status_text'] = Yii::app()->params['message_status'][$v->status];
			$message[$k]['status_color'] = Yii::app()->params['status_color'][$v->status];
			
			$_replys = Reply::model()->with('members')->findAll(array(
					'condition' => 'message_id=:message_id',
					'params'	=> array(':message_id' => $v->id),
			));
			
			if(!empty($_replys))
			{
				foreach ($_replys AS $kk => $vv)
				{
					$message[$k]['replys'][$kk] = $vv->attributes;
					$message[$k]['replys'][$kk]['create_time'] 	= date('Y-m-d H:i:s',$vv->create_time);
					$message[$k]['replys'][$kk]['user_name'] 	= ($vv->user_id == -1)?'行政MM说':$vv->members->name;
				}
			}
		}
		
		$this->render('lookmenu',array(
			'menus' 	=> $data,
			'shop' 		=> $shopData,
			'pages'		=> $pages,
			'message'	=> $message,
		));
	}
	
	//查看购物车
	public function actionLookCart()
	{
	    $memberInfo = Members::model()->find('id=:id',array(':id' => Yii::app()->user->member_userinfo['id']));
			
		//如果是常驻员工且只订购了一份饭，则减免全部订购费
		if ($memberInfo->resident==1 && $this->order['Count']==1)
		{
		    $this->order['Total'] = 0;
		    
		    foreach ($this->order['Items'] AS $k => $v)
				{
					//$this->order['Items'][$k]['Price'] = $v['Price'];
				    //$this->order['Items'][$k]['smallTotal'] = $v['Count'] * $v['Price'];
				    $this->order['Items'][$k]['Price'] = 0;
				    $this->order['Items'][$k]['smallTotal'] = $v['Count'] * $v['Price'];
				}
		}
		
		$this->render('lookcart',array('order' => $this->order));
	}
	
	//确认下单
	public function actionConfirmOrder()
	{
		//确认订单之前查看用户余额够不够付
		$memberInfo = Members::model()->find('id=:id',array(':id' => Yii::app()->user->member_userinfo['id']));
		
	    //如果常驻员工只定了一份饭，则将费用减免掉
		if ($memberInfo->resident ==1 and $this->order['Count'] ==1)
		{
		    $this->order['Total'] = 0;
		    
		    foreach ($this->order['Items'] AS $k => $v)
				{
					$this->order['Items'][$k]['Price'] = 0;
				    $this->order['Items'][$k]['smallTotal'] = 0;
				}
		}
		
		//服务器端判断订餐时间有没有超过限制时间
	    $shop_id = Yii::app()->request->getParam('shop_id');
		
	    if(!Yii::app()->check_time->isShopOntime($shop_id))
		{
			throw new CHttpException(404,Yii::t('yii','你提交的订餐不在该餐订餐时间内'));
		}
		
		//如果状态禁用，不允许提交订单
	    if($memberInfo->status == 2)
		{
			throw new CHttpException(404,Yii::t('yii','你的帐号被管理员禁用，请联系行政MM处理！'));
		}
		
		
		if($memberInfo->balance < $this->order['Total'] && !in_array(Yii::app()->user->member_userinfo['id'], Yii::app()->params['allow_user_id'])) 
		{
			throw new CHttpException(404,Yii::t('yii','您的账户余额不足，不能下单，到行政MM交钱吧！'));
		}
				
		
		//构建数据
		$foodOrder = new FoodOrder();
		$foodOrder->shop_id = $this->order['shop_id'];
		$foodOrder->order_number = date('YmdHis',time()) . Common::getRandNums(6);
		$foodOrder->food_user_id = Yii::app()->user->member_userinfo['id'];
		$foodOrder->total_price = $this->order['Total'];
		$foodOrder->create_time = time();
		$foodOrder->product_info = serialize($this->order['Items']);
		
		if($foodOrder->save())
		{
			//记录订单动态
			$foodOrderLog = new FoodOrderLog();
			$foodOrderLog->food_order_id = $foodOrder->id;
			$foodOrderLog->create_time = time();
			if($foodOrderLog->save())
			{
				//清空购物车
				unset(Yii::app()->request->cookies['cart']);
				$this->redirect(Yii::app()->createUrl('site/orderok',array('ordernumber' => $foodOrder->order_number)));
			}
		}
		else 
		{
			throw new CHttpException(404,Yii::t('yii','下单失败'));
		}
	}
	
	//下单成功页面
	public function actionOrderOk()
	{
		//判断有没有该订单
		$ordernumber = Yii::app()->request->getParam('ordernumber');
		if(!$ordernumber)
		{
			throw new CHttpException(404,Yii::t('yii','没有订单号'));
		}
		
		//根据当前用户的id与订单号查询出有没有该订单
		$criteria=new CDbCriteria;
		$criteria->select = 'order_number,total_price,create_time';
		$criteria->condition = 'order_number = :order_number AND food_user_id = :food_user_id';
		$criteria->params = array(':order_number' => $ordernumber,':food_user_id' => Yii::app()->user->member_userinfo['id']);
		$data = FoodOrder::model()->find($criteria);
		if(!$data)
		{
			throw new CHttpException(404,Yii::t('yii','您没有该订单'));
		}
		
		$data = CJSON::decode(CJSON::encode($data));
		$data['create_time'] = date('Y年m月d日 H时i分s秒',$data['create_time']);
		$data['username'] = Yii::app()->user->member_userinfo['username'];
		$this->render('orderok',array('order_info' => $data));
	}
	
	//用户中心
	public function actionMemberCenter()
	{
		//查询出用户的基本信息
		$member_id = Yii::app()->user->member_userinfo['id'];
		$criteria=new CDbCriteria;
		$criteria->select = 'name,sex,avatar,email,balance,resident,status';
		$criteria->condition = 'id=:id';
		$criteria->params = array(':id' => $member_id);
		$memberData = Members::model()->find($criteria);
		$memberData = CJSON::decode(CJSON::encode($memberData));
		$this->render('membercenter',array('member' => $memberData));
	}
	
	//查询用户自己的订单
	public function actionMyOrder()
	{
		$is_today = Yii::app()->request->getParam('today');
		$member_id = Yii::app()->user->member_userinfo['id'];
		$criteria = new CDbCriteria;
		$criteria->order = 't.create_time DESC';
		$criteria->select = '*';
		$criteria->condition = 'food_user_id=:food_user_id';
		$criteria->params = array(':food_user_id' => $member_id);
		
		//构建分页
		$count=FoodOrder::model()->count($criteria);
		$pages = new CPagination($count);
 		$pages->pageSize = Yii::app()->params['pagesize'];
		$pages->applyLimit($criteria);
		//按条件获取数据
		
		$model = FoodOrder::model()->with('shops','food_log')->findAll($criteria);
		$orderData = array();
		foreach ($model AS $k => $v)
		{
			if($is_today)
			{
				//只取今天的订单
				if(date('Ymd',$v->create_time) != date('Ymd',time()))
				{
					continue;
				}
			}
			else 
			{
				//排除今天的订单
				if(date('Ymd',$v->create_time) == date('Ymd',time()))
				{
					continue;
				}
			}

			$orderData[$k] = $v->attributes;
			$orderData[$k]['shop_name'] = $v->shops->name;
			$orderData[$k]['product_info'] = unserialize($v->product_info);
			$orderData[$k]['create_order_date'] = date('Y-m-d',$v->create_time);
			$orderData[$k]['create_time'] = date('H:i:s',$v->create_time);
			$orderData[$k]['status_text'] = Yii::app()->params['order_status'][$v->status];
			//订单状态日志
			$status_log = CJSON::decode(CJSON::encode($v->food_log));
			foreach ($status_log AS $kk => $vv)
			{
				$status_log[$kk]['status_text'] = Yii::app()->params['order_status'][$vv['status']];
				$status_log[$kk]['create_time'] = date('H:i:s',$vv['create_time']);
			}
			$orderData[$k]['status_log'] = $status_log;
		}
		$cur_title = $is_today?'今日订单':'历史订单';
		$this->render('myorder',array('order' => $orderData,'cur_title' => $cur_title,'pages' => $pages));
	}
	
	//前台会员登陆界面
	public function actionLogin()
	{
		//如果已经登陆就直接跳到订单中心（用户中心）
		if(isset(Yii::app()->user->member_userinfo))
		{
			$this->redirect(Yii::app()->createUrl('site/membercenter'));
		}
		else 
		{
			$this->render('login');	
		}
	}
	
	//执行登录操作
	public function actionDoLogin() 
	{
		$name = Yii::app()->request->getParam('name');
		$password = Yii::app()->request->getParam('password');

		if(!$name)
		{
			$this->errorOutput(array('error' => 1));
		}
		
		if(!$password)
		{
			$this->errorOutput(array('error' => 2));
		}
		
		//利用MemberIdentity来验证
		$identity=new MemberIdentity($name,$password);
		$identity->authenticate();
		
		//登录成功
		if($identity->errorCode===MemberIdentity::ERROR_NONE)
		{
			$duration = 3600*24;//保持一个月
			Yii::app()->user->login($identity,$duration);
			$this->errorOutput(array('error' => 4));
		}
		else
		{
			$this->errorOutput(array('error' => 3));		
		}		
	}
	
	//会员注册页面
	public function actionRegister()
	{
		$this->render('register');
	}
	
	//执行注册操作
	public function actionDoRegister()
	{
		$name = Yii::app()->request->getPost('name');
		$resident = Yii::app()->request->getPost('resident');
		$password1 = Yii::app()->request->getPost('password1');
		$password2 = Yii::app()->request->getPost('password2');

		if(!$name)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '姓名不能为空'));
		}
		else if(strlen($name) > 15)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '姓名太长不能超过15个字符'));
		}

		if(!$password1 || !$password2)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '密码不能为空'));
		}
		else if(strlen($password1) > 15 || strlen($password2) > 15)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '两次密码不能超过15个字符'));
		}
		else if($password1 !== $password2)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '两次密码不相符'));
		}
		
		//判断该用户是不是已经存在了
		$_member = Members::model()->find('name=:name',array(':name' => $name));
		if($_member)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '该用户已经存在'));
		}
		
		//随机长生一个干扰码
		$salt = Common::getGenerateSalt();
		$model = new Members();
		$model->name = $name;
		$model->salt = $salt;
		$model->password = md5($salt . $password1);
		$model->create_time = time();
		$model->update_time = time();
		$model->resident = $resident;
		if($model->save())
		{
			$model->order_id = $model->id;
			$model->save();
			$this->output(array('success' => 1,'successText' => '注册成功'));
		}
		else 
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '注册失败'));
		}
	}
	
	//会员退出
	public function actionLogout()
	{
		if(isset(Yii::app()->user->member_userinfo))
		{
			unset(Yii::app()->user->member_userinfo);
		}
		$this->redirect(array('site/login'));
	}
	
	//修改密码页面
	public function actionmodifyPassword()
	{
		$this->render('modifypassword');
	}
	
	//确认修改
	public function actionDomodify()
	{
		$cur_password = Yii::app()->request->getPost('cur_password');
		$new_password = Yii::app()->request->getPost('new_password');
		$comfirm_password = Yii::app()->request->getPost('comfirm_password');

		if(!$cur_password)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '当前密码不能为空'));
		}

		if(!$new_password || !$comfirm_password)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '新密码不能为空'));
		}
		else if(strlen($new_password) > 15 || strlen($comfirm_password) > 15)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '新密码不能超过15个字符'));
		}
		else if($new_password !== $comfirm_password)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '两次密码不相符'));
		}
		
		//判断该用户是不是已经存在了
		$_member = Members::model()->find('id=:id',array(':id' => Yii::app()->user->member_userinfo['id']));
		if(!$_member)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '当前用户不存在'));
		}
		else if(md5($_member->salt . $cur_password) != $_member->password)
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '当前密码输入错误'));
		}
		
		//随机长生一个干扰码
		$salt = Common::getGenerateSalt();
		$_member->salt = $salt;
		$_member->password = md5($salt . $new_password);
		$_member->update_time = time();
		if($_member->save())
		{
			$this->output(array('success' => 1,'successText' => '修改成功'));
		}
		else 
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '修改失败'));
		}
	}
	
	//系统公告
	public function actionSystemNotice()
	{
		//查询出公告数据
		$notice = Announcement::model()->findAll(array('order' => 'create_time DESC','condition' => 'status=:status','params'=>array(':status'=>2)));
		$notice = CJSON::decode(CJSON::encode($notice));
		foreach($notice AS $k => $v)
		{
			$notice[$k]['create_time'] = date('Y-m-d',$v['create_time']);
		}
		$this->render('systemnotice',array('announce' => $notice));
	}
	
	//用户取消订单
	public function actionCancelOrder()
	{
		if(Yii::app()->request->isAjaxRequest)
		{
			$food_order_id = Yii::app()->request->getParam('id');
			if(!$food_order_id)
			{
				$this->errorOutput(array('errorCode' => 1,'errorText' => '没有id'));
			}
			
			//判断当前时间有没有已经过了订餐的时间，如果过了订餐的时间，就不能取消订单了，只能让妹子操作后台取消
    		if(!Yii::app()->check_time->isOnTime())
    		{
    			$this->errorOutput(array('errorCode' => 1,'errorText' => '已经过了订餐时间，您暂时不能取消订单，如果确实需要取消，请联系前台妹子'));
    		}
			
			$orderInfo = FoodOrder::model()->find('id=:id AND food_user_id=:food_user_id',array(':id' => $food_order_id,':food_user_id' => Yii::app()->user->member_userinfo['id']));
			if(!$orderInfo)
			{
				$this->errorOutput(array('errorCode' => 1,'errorText' => '该订单不存在'));
			}
			else if($orderInfo->status != 1)
			{
				$this->errorOutput(array('errorCode' => 1,'errorText' => '该订单不能被取消'));
			}
			
			$orderInfo->status = 3;
			if($orderInfo->save())
			{
				//创建一条订单日志
				$foodOrderLog = new FoodOrderLog();
				$foodOrderLog->food_order_id = $food_order_id;
				$foodOrderLog->status = $orderInfo->status;
				$foodOrderLog->create_time = time();
				if($foodOrderLog->save())
				{
					$this->output(array('success' => 1,'successText' => '取消订单成功'));
				}
				else 
				{
					$this->errorOutput(array('errorCode' => 1,'errorText' => '更新订单状态失败'));
				}
			}
			else 
			{
				$this->errorOutput(array('errorCode' => 1,'errorText' => '取消订单失败'));
			}
		}
		else 
		{
			throw new CHttpException(404,Yii::t('yii','非法操作'));
		}
	}
	
	//美食分享
	public function actionFoodShare()
	{
		$this->render('foodshare');
	}
	
	//提交留言
	public function actionSubmitMessage()
	{
		$content = Yii::app()->request->getParam('content');
		$validate_code = Yii::app()->request->getParam('validate_code');
		$shop_id = Yii::app()->request->getParam('shop_id');
		
		if(!isset(Yii::app()->user->member_userinfo))
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '你还未登录，请先去登录'));
		}
		else 
		{
			$user_id = Yii::app()->user->member_userinfo['id'];
		}
		
		if(!$content)
		{
			$this->errorOutput(array('errorCode' => 2,'errorText' => '留言内容不能为空'));
		}
		
		if(!$validate_code)
		{
			$this->errorOutput(array('errorCode' => 3,'errorText' => '验证码不能为空'));
		}
		
		if(!$shop_id)
		{
			$this->errorOutput(array('errorCode' => 4,'errorText' => '没有餐类id'));
		}
		
		//验证验证码是否正确
		if(!$this->createAction('captcha')->validate($validate_code,false))
		{
			$this->errorOutput(array('errorCode' => 5,'errorText' => '验证码有误'));
		}
		
		$model = new Message();
		$model->shop_id = $shop_id;
		$model->user_id = $user_id;
		$model->content = $content;
		$model->create_time = time();
		if($model->save())
		{
			$model->order_id = $model->id;
			if($model->save())
			{
				$this->output(array('success' => 1,'successText' => '留言成功'));
			}
			else 
			{
				$this->errorOutput(array('errorCode' => 6,'errorText' => '留言失败'));
			}
		}
		else 
		{
			$this->errorOutput(array('errorCode' => 6,'errorText' => '留言失败'));
		}
	}
	
	//回复留言
	public function actionReplyMessage()
	{
		$message_id = Yii::app()->request->getParam('reply_id');
		$reply_content = Yii::app()->request->getParam('reply_content');
		
		if(!isset(Yii::app()->user->member_userinfo))
		{
			$this->errorOutput(array('errorCode' => 1,'errorText' => '你还未登录，请先去登录'));
		}
		else 
		{
			$user_id = Yii::app()->user->member_userinfo['id'];
		}
		
		if(!$reply_content)
		{
			$this->errorOutput(array('errorCode' => 2,'errorText' => '回复内容不能为空'));
		}
		
		if(!$message_id)
		{
			$this->errorOutput(array('errorCode' => 3,'errorText' => '未选择回复留言'));
		}
		
		$model = new Reply();
		$model->message_id = $message_id;
		$model->user_id = $user_id;
		$model->content = $reply_content;
		$model->create_time = time();
		if($model->save())
		{
			$this->output(array('success' => 1,'successText' => '回复成功'));
		}
		else 
		{
			$this->errorOutput(array('errorCode' => 4,'errorText' => '回复失败'));
		}
	}
	
	//查看消费记录（充值与扣款的记录）
	public function actionSeeConsume()
	{
	    $userId = Yii::app()->user->member_userinfo['id'];
	    $criteria = new CDbCriteria;
		$criteria->condition = 't.user_id=:user_id';
		$criteria->order = 't.create_time DESC';
		$criteria->params=array(':user_id' => $userId);
		
		$count = RecordMoney::model()->count($criteria);
		//构建分页
		$pages = new CPagination($count);
 		$pages->pageSize = Yii::app()->params['pagesize'];
		$pages->applyLimit($criteria);
		$record = RecordMoney::model()->with('members')->findAll($criteria);
		$data = array();
		foreach ($record AS $k => $v)
		{
			$data[$k] = $v->attributes;
			$data[$k]['type_text'] = Yii::app()->params['record_money'][$v['type']];
			$data[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
			$data[$k]['user_name'] = $v->members->name;
		}
		$this->render('seeconsume',array(
			'data' => $data,
			'pages'	=> $pages
		));
	}
	
    //LCF:订单前台展示
	//如果有查询时间输入
	public function actionOrderView()
	{
		//创建查询条件
		$criteria = new CDbCriteria();
		$criteria->order = 't.create_time DESC';//按时间倒序排
		
		//如果没有指定日期，默认查询当天的订单统计
		$date = Yii::app()->request->getParam('date');
		if($date)
		{
			$today = strtotime(date($date));
			if(!$today)
			{
				throw new CHttpException(404,'日期格式设置有误');
			}
			else if($today > time()) 
			{
				throw new CHttpException(404,'设置的日期不能超过今天');
			}
			$tomorrow = $today + 24*3600;
		}
		else 
		{
			$today = strtotime(date('Y-m-d'));
			$tomorrow = strtotime(date('Y-m-d',time()+24*3600));
		}
		
		$criteria->condition = '(t.status = :status1 OR t.status = :status2) AND t.create_time > :today AND t.create_time < :tomorrow';
		$criteria->params = array(':status1' => 1,':status2' => 2,':today' => $today,':tomorrow' => $tomorrow);
		$model = FoodOrder::model()->with('shops','members')->findAll($criteria);
		$data = array();
		$_total_price = 0;
		$tongji = array();
		//存储所有订餐的人员名单
		$orderList = array();
		
		//处理查询得到的每一条订单
		//k表示键值，v表示每一条订单
		foreach($model AS $k => $v)
		{
			$_total_price += $v->total_price;
			$data[$k] = $v->attributes;
			$data[$k]['product_info'] = unserialize($v->product_info);
			$data[$k]['shop_name'] = $v->shops->name;
			$data[$k]['user_name'] = $v->members->name;
			$data[$k]['create_time'] = date('Y-m-d H:i:s',$v->create_time);
			$data[$k]['status_text'] = Yii::app()->params['order_status'][$v->status];
			$data[$k]['status_color'] = Yii::app()->params['status_color'][$v->status];	
			//统计
			$tongji[$v->shop_id]['name'] = $v->shops->name;
			$tongji[$v->shop_id]['product'][] = unserialize($v->product_info);
			
			//将所有订单按shopName,username,orderinfo存储起来
			$orderList[$v->shop_id]['food_class'] = $v->shops->name;
			$orderList[$v->shop_id]['order'][$v->food_user_id]['user_name'] = $v->members->name;
			$orderList[$v->shop_id]['order'][$v->food_user_id]['order_info'][] = unserialize($v->product_info);
		}
		
		//按餐类和地点重新组织为orderviewlist
		$orderViewList = array();
		
		//统计结果
		$result = array();
		$foodTotalCount = 0;
		foreach ($orderList AS $k => $v)
		{
			$result[$k]['name'] = $v['food_class'];
			$orderViewList[$k]['foodClass'] = $v['food_class'];;
			
			$shop_total_price = 0;
			$foodsShopTotalCount = 0;
			foreach($v['order'] AS $lk => $lv)
			{
			    //$orderViewList[$k]['order'][$lk]['userName']=$lv['user_name'];
			    foreach($lv['order_info'] As $_k => $_v)
			    {
				foreach ($_v AS $kk => $vv)
				{
					$shop_total_price += $vv['smallTotal'];
					$foodsShopTotalCount += $vv['Count'];
					$result[$k]['product'][$vv['Id']]['name'] = $vv['Name'];
					if($result[$k]['product'][$vv['Id']]['count'])
					{
						$result[$k]['product'][$vv['Id']]['count'] += $vv['Count'];
					}
					else 
					{
						$result[$k]['product'][$vv['Id']]['count'] = $vv['Count'];
					}
					
				    if($result[$k]['product'][$vv['Id']]['smallTotal'])
					{
						$result[$k]['product'][$vv['Id']]['smallTotal'] += $vv['smallTotal'];
					}
					else 
					{
						$result[$k]['product'][$vv['Id']]['smallTotal'] = $vv['smallTotal'];
					}
					
					//LCF:统计orderViewList列表
					//orderview
					if ($orderViewList[$k]['locations'][$vv['Id']]['count'])
					{
					    $orderViewList[$k]['locations'][$vv['Id']]['count'] += $vv['Count'];
					}
					else
					{
					    $orderViewList[$k]['locations'][$vv['Id']]['count'] = $vv['Count'];
					}
					
					$orderViewList[$k]['locations'][$vv['Id']]['locationName'] = $vv['Name'];
					$orderViewList[$k]['locations'][$vv['Id']]['orderItems'][$lk]['userName'] = $lv['user_name'];
					$orderViewList[$k]['locations'][$vv['Id']]['orderItems'][$lk]['count'] += $vv['Count'];;					
					
				}
			}
			}
			
			$result[$k]['shop_total_price'] = $shop_total_price;
			$orderViewList[$k]['totalCount'] = $foodsShopTotalCount;
			$foodTotalCount += $foodsShopTotalCount;
		}
		
		//输出到前端
		$this->render('orderView', array(
		    'foodTotalCount' => $foodTotalCount,
		    'orderViewList' => $orderViewList,
			'data' 	=> $data,
			'statistics' => $result,
			'total_price' => $_total_price,
			'date' => $date,
		));
	}
}