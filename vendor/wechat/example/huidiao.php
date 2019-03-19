<?php


class PayNotifyCallBack  
{

	//重写回调处理函数
	public function test()
	{
		
			
				$this->module_pay->order_paid();

	}
}

$notify = new PayNotifyCallBack();
var_dump($notify->test());
