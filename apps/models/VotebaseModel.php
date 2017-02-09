<?php
/**
 * Created by PhpStorm.
 * User: tanghui
 * Date: 2017/2/9
 * Time: 上午9:56
 */

namespace app\models;
use Felix;

class VotebaseModel extends Felix\Model{



    function getData()
    {
        $this->db->limit(10);
        $query = $this->db->get('vote_base');

        return $query->result_array();
    }


    function updateTitle($id,$title)
    {
        $this->db->where('vote_id', $id);

        return $this->db->update("vote_base",['title'=>$title]);

    }

    function insert()
    {
        $this->db->insert("vote_base",['title'=>'sdsds']);
        return $this->db->insert_id();
    }

}