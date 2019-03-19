<?php

class ModuleVideoFoodsvideo extends Module{

	const TABLE = 'foods_video';

	// 获取列表
    // 商品列表
    public function getList($limit,$search=[],$order='id desc'){
        $table = DB_PREFIX . self::TABLE;
        $where = ' 1=1 ';
        // 是视频名称
        if(isset($search['shop_id'])){
            $where .= 'and  shop_id = "'.$search['shop_id'].'" ';
        }
        // 门店id
        if(isset($search['video_name'])){
            $where .=" and video_name like '%".$search['video_name']."%' ";
            //$where .= ' and   video_name like %"'.$search['search_keyword'].'%" ';
        }
        $datalist = $this->db->table($table)->where($where)->order($order)->limit($limit)->selectlist();
        if($datalist){
            foreach ($datalist as $key=>&$val){
                if($val['created_time']){
                    $val['created_time']=date('Y-m-d H:i:s');
                }else{
                    $val['created_time']="00-00-00 00:00:00";
                }

            }
        }

        $jsondata['datalist'] = $datalist;
        $jsondata['status'] = 'Success';


        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];


    }
    // 修改
    public function edit($id,$bind)
    {
        $table  = DB_PREFIX . self::TABLE;
        $where  = ' `id` = "' . $id . '"';
        $bind['update_time']=time();
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        if($status){
            return [
                'status' => 'Success',
                'message' => '修改成功'
            ];
        }else{
            return [
                'status' => 'Failed',
                'message' => '修改失败'
            ];
        }

    }

    // 新增
    public function add($bind){
        $table  = DB_PREFIX . self::TABLE;
        $bind['created_time']=time();
        $bind['update_time']=time();
        $status = $this->db->table($table)->bind($bind)->insert();
        if($status){
            return [
                'status' => 'Success',
                'message' => '新增成功'
            ];
        }else{
            return [
                'status' => 'Failed',
                'message' => '新增失败'
            ];
        }

    }

    // 获取Info
    public function getInfo($id)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"';
        $data  = $this->db->table($table)->where($where)->find();
        return $data ?: false;
    }

    public function delete($id)
    {

        if(!$id){
            $message = '删除失败,缺少必要参数';
            goto error;
        }
        // 删除
        $table  = DB_PREFIX . self::TABLE;
        $status = $this->db->table($table)->where('id="'.$id.'"')->limit(1)->delete();
        if(!$status) {
            $message = '删除失败';
            goto error;
        }

        return [
            'status' => 'Success',
            'message' => '删除成功'
        ];
        error:
        return [
            'status' => 'Failed',
            'message' => $message,
        ];
    }

    /*
     * 接口返回数据
     * type:0(全部)；1（最新上线）；2（人气最高）；3（我的收藏）
     */

    public function get_list($data=[]){
        $table = DB_PREFIX . self::TABLE;

        $data['shop_id'] = $data['shop_id']?$data['shop_id']:"";
        $where=" 1=1 ";
        if($data['shop_id']!=""){
            $where.=" and shop_id =".$data['shop_id'];
        }
        if($data['video_name']){
            $where.=" and (video_name like '%".$data['video_name']."%' or video_flag like '%".$data['video_name']."%')";
        }

        $order ="";
        if(!$data['type']){//所有 all
            $order="  sort asc,id desc ";
        }
        if($data['type']==1){
            $order="  created_time DESC";
        }
        if($data['type']==2){
            $order="   popularity DESC";
        }
        if($data['type']==3){

            $_sql="SELECT v.id FROM `blwx_foods_video`   v INNER JOIN blwx_foods_collection c on v.id=c.video_id and c.wx_open_id='{$this->session->get('openid')}'  ";
            $collection_video =    $this->db->query($_sql);
            $_videoids="";
            foreach ($collection_video as $k=>$v)
            {
                if($_videoids=="")
                {
                    $_videoids=$v['id'];
                }else{

                    $_videoids=$_videoids.",".$v['id'];
                }
            }

            if($_videoids!="")
            {
                $where =$where." and id in ({$_videoids})";

            }else{
                $where=" 1=2 ";
            }

        }

        $datalist = $this->db->table($table)->where($where)->order($order)->limit($data['limit'])->selectlist();

        foreach ($datalist as $k=>$v)
        {
            $count_sql="SELECT COUNT(id) AS num  FROM  `blwx_foods_fabulous`  WHERE video_id =".$v['id']." AND fabulous = 1";
            $v['fabulous_num']=$this->db->query($count_sql)['num'];
            $datalist[$k]=$v;
        }

        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];

        //dyj

//        //获取基本数据
//        $where=" where 1=1 ";
//        if($data['shop_id']!=""){
//            $where.=" and v.shop_id =".$data['shop_id'];
//        }
//        if($data['video_name']){
//            $where.=" and (.video_name like'%".$data['video_name']."%' or v.video_flag like '%".$data['video_name']."%')";
//        }
//        $sql ="SELECT * FROM `blwx_foods_video` v ";
//        $sql1 ="SELECT count(*) FROM `blwx_foods_video` v ";
//        //分页
//        $now_num=($data['page']-1)*4;
//        $limit=" LIMIT ".$now_num.",4";
//
//        if(!$data['type']){//所有 all
//            $order=" ";
//        }
//        if($data['type']==1){
//            $order=" ORDER BY v.created_time DESC";
//        }
//        if($data['type']==2){
//            $order=" ORDER BY v.popularity DESC";
//        }
//        if($data['type']==3){
//            $sql="SELECT * FROM `blwx_foods_video` v INNER JOIN blwx_foods_collection c on v.id=c.video_id  ";
//        }
//        $sql1="SELECT count(*) FROM `blwx_foods_video` v INNER JOIN blwx_foods_collection c on v.id=c.video_id  ";
//        $sql=$sql.$where.$order.$limit;
//        $data=$this->db->query($sql);
//        $total_num=$this->db->query($sql1.$where.$order)['count(*)'];
//        if($data){
//            foreach ($data as $key=>&$v){
//                $count_sql="SELECT COUNT(DISTINCT wx_open_id) AS num  FROM  `blwx_foods_fabulous`  WHERE video_id =".$v['id']." AND fabulous = 1";
//                $val['num']=$this->db->query($count_sql)['num'];
//            }
//        }
//
//        return [
//            'datacount' => $total_num,
//            'datalist'  => $data,
//        ];

    }


    public  function  getdata($limit=10,$_where="",$orderby="id desc"){
        $table = DB_PREFIX . self::TABLE;
        $s_storeid = $this->session->get('storeid');

        $where ="";
        if($_where!="")
        {
            $where=$_where;
        }

        $datalist = $this->db->table($table)->where($where)->order($orderby)->limit($limit)->selectlist();

        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }



    public  function  getvideodetailByid($id){
        $table = DB_PREFIX . self::TABLE;

        $data = $this->db->table($table)->where("id ={$id}")->find();



        return $data;
    }


    //getpinlunlistapi

    public  function  getpinlunlistapi($limit=10,$video_id,$orderby="id desc"){
        $table ="blwx_foods_comment";

        if($video_id=="" || empty($video_id))
        {
        die("123");
        }


        $datalist = $this->db->table($table)->where(" video_id={$video_id} ")->order($orderby)->limit($limit)->selectlist();

        foreach ($datalist as $k=>$v)
        {

            $user_sql="SELECT  *  FROM  `blwx_member_user`  WHERE wx_openid ='".$v['wx_open_id']."'" ;
            $v['userdata']=$this->db->query($user_sql);
            if($v['parent_id']!="" && !empty($v['parent_id']) )
            {
                $comment_sql="SELECT  *  FROM  `blwx_foods_comment`  WHERE id =".$v['parent_id'] ;
                $parant_comment = $this->db->query($comment_sql);
                $user_sql="SELECT  *  FROM  `blwx_member_user`  WHERE wx_openid ='".$parant_comment['wx_open_id']."'" ;
                $_userdata=$this->db->query($user_sql);
                $v['comment']= $v['comment'] ."  回复// {$_userdata['nickname'] }:". $parant_comment["comment"] ;
             }
             //处理时间
            //$v['created_time']=date($v['created_time'],"Y-m-d h:m:s");
            $v['created_time']=date('Y-m-d',$v['created_time']);

            $datalist[$k]=$v;

        }

        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    //getvideofabulousByid
    //获取点赞

    public function getvideofabulousByid($id){

        $data = $this->db->table('blwx_foods_fabulous')->where("video_id ={$id} and fabulous=1 ")->selectlist();
         return $this->db->datacount?$this->db->datacount:0;

    }
    //getvideocollectByid
    //获取收藏数量
    public function getvideocollectByid($id){
        $data = $this->db->table('blwx_foods_collection')->where("video_id ={$id} ")->selectlist();
        return $this->db->datacount?$this->db->datacount:0;

    }

    //增加或减少收藏
    //$type 1:表收藏 0：表取消收藏
    public function insertcollect($id){
        $result=['status'=>true,'data'=>'成功'];

        $data = $this->db->table('blwx_foods_collection')->where("video_id ={$id} and wx_open_id='{$this->session->get('openid')}' ")->selectlist();
        $data=$this->db->datacount?$this->db->datacount:0;

        if($data){
            $this->db->table("blwx_foods_collection")->where("video_id ={$id} and wx_open_id='{$this->session->get('openid')}' ")->delete();
            $result['data']="取消收藏成功";
        }else{

            $inesrData['wx_open_id']=$this->session->get('openid');
            $inesrData['video_id']=$id;
            $inesrData['created_time']=time();
            $this->db->table("blwx_foods_collection")->bind($inesrData)->insert();
            $result['data']="收藏成功";
        }
        return $result;

    }

    //点赞和消赞

    public function insertFabulous($id){
        $result=['status'=>true,'data'=>'成功'];
        $data = $this->db->table('blwx_foods_fabulous')->where("video_id ={$id} and wx_open_id='{$this->session->get('openid')}'  and fabulous=1 ")->selectlist();
        $data =$this->db->datacount?$this->db->datacount:0;
        if($data){
            $updateData['update_time']=time();
            $updateData['fabulous']=0;
            $this->db->table('blwx_foods_fabulous')->where("video_id ={$id} and wx_open_id='{$this->session->get('openid')}'  and fabulous=1 ")->bind($updateData)->update();

            $result['data']="取消点赞成功";

        }else{

            $inesrData['wx_open_id']=$this->session->get('openid');
            $inesrData['video_id']=$id;
            $inesrData['fabulous']=1;
            $inesrData['created_time']=time();
            $this->db->table("blwx_foods_fabulous")->bind($inesrData)->insert();


            $result['data']="点赞成功";
        }
        return $result;


    }

    //增加人气

    public function updatepopularityapi($id){

        $sql=" update  blwx_foods_video set popularity = popularity+1 where id ={$id}";

        $result=$this->db->exec($sql);

    }



    //判断是否收藏

    public function iscollectByid($id){
        $result=['status'=>true,'data'=>'成功'];

        $data = $this->db->table('blwx_foods_collection')->where("video_id ={$id} and wx_open_id='{$this->session->get('openid')}' ")->selectlist();
        return $this->db->datacount?$this->db->datacount:0;
    }
    //判断是否点赞
    public function isfabulousByid($id){
        $data = $this->db->table('blwx_foods_fabulous')->where("video_id ={$id} and wx_open_id='{$this->session->get('openid')}'  and fabulous=1 ")->selectlist();
        return $this->db->datacount?$this->db->datacount:0;
    }

    //评论列表

    public  function  getcommentlist($limit=10,$id=1,$order='id desc'){
        $datalist = $this->db->table('blwx_foods_comment')->where( " video_id={$id} ")->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }


//删除评论
 public function deletecomment($id)
    {

        if(!$id){
            $message = '删除失败,缺少必要参数';
            goto error;
        }
        // 删除
        
        $status = $this->db->table('blwx_foods_comment')->where('id="'.$id.'"')->delete();
        if(!$status) {
            $message = '删除失败';
            goto error;
        }

        return [
            'status' => 'Success',
            'message' => '删除成功'
        ];
        error:
        return [
            'status' => 'Failed',
            'message' => $message,
        ];
    }
    /*
     * 获取新闻列表
     */
    public function getListBytitle($limit,$search=[],$order='id desc'){
        $table = "blwx_foods_news";
        $where = ' 1=1 ';

        if(isset($search['title'])){
            $where .=" and title like '%".$search['title']."%' ";
        }
        $datalist = $this->db->table($table)->where($where)->order($order)->limit($limit)->selectlist();

        $jsondata['datalist'] = $datalist;
        $jsondata['status'] = 'Success';


        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];


    }

    public function delNewById($id)
    {

        if(!$id){
            $message = '删除失败,缺少必要参数';
            goto error;
        }
        // 删除
        $status = $this->db->table("blwx_foods_news")->where('id="'.$id.'"')->limit(1)->delete();
        if(!$status) {
            $message = '删除失败';
            goto error;
        }

        return [
            'status' => 'Success',
            'message' => '删除成功'
        ];
        error:
        return [
            'status' => 'Failed',
            'message' => $message,
        ];
    }

    // 新增新闻
    public function addnew($bind){
        $table  = "blwx_foods_news";
        $bind['created_time']=date('Y-m-d H:i:s');
        $bind['update_time']=date('Y-m-d H:i:s');
        $status = $this->db->table($table)->bind($bind)->insert();
        if($status){
            return [
                'status' => 'Success',
                'message' => '新增成功'
            ];
        }else{
            return [
                'status' => 'Failed',
                'message' => '新增失败'
            ];
        }

    }

    /*
     * 修改新闻
     */
    public function editnew($id,$bind)
    {
        $table  = "blwx_foods_news";
        $where  = ' `id` = "' . $id . '"';
        $bind['update_time']=date('Y-m-d H:i:s');
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        if($status){
            return [
                'status' => 'Success',
                'message' => '修改成功'
            ];
        }else{
            return [
                'status' => 'Failed',
                'message' => '修改失败'
            ];
        }

    }
    /*
     * 获取新闻详情
     */

    public function getnewInfo($id)
    {
        $table = "blwx_foods_news";
        $where = ' `id` = "' . $id . '"';
        $data  = $this->db->table($table)->where($where)->find();
        return $data ?: false;
    }















}