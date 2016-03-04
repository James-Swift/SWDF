<?php

	class SWDF_DB extends PDO {
	
		public function __construct($dsn,$user,$pass,$opts=null){
			global $_SWDF;
			if ($opts===null){
				$opts=Array(
					PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
					PDO::ATTR_PERSISTENT=>true,
					PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,
				);
				if (substr($dsn,0,5)=="mysql"){
					$opts[PDO::MYSQL_ATTR_USE_BUFFERED_QUERY]=true;
				}
			}
			parent::__construct($dsn,$user,$pass,$opts);
		}
		
		public function select($info,$from,$where=NULL,$order_by=NULL,$id_field=NULL,$return_type=NULL){
			$vals=Array();
                        $sql="";
			if (is_array($info)){
				$sql.="SELECT ".implode(",", $info);
			} else {
				$sql.="SELECT $info";
			}
			
			if (is_array($from)){
				$sql.=" FROM ".implode(",", $from);
			} else {
				$sql.=" FROM $from";
			}
			
			if ($where!=""){
				if (is_array($where) && sizeof($where)>0){
					$i=0;
					$sql.=" WHERE ";
					foreach($where as $id=>$val){
						if ($i>0){ $sql.=" AND "; }
						$sql.=$id."=?";
						$vals[]=$val;
						$i++;
					}	
				} else if (is_string($where)){
					$sql.=" WHERE $where";
				}
			}
			
			if ($order_by!=""){
				if (is_array($from)){
					$sql.=" ORDER BY ".implode(",", $order_by);
				} else {
					$sql.=" ORDER BY $order_by";
				}
			}
			
			try {
				$stat=$this->prepare($sql);
				if ($return_type!==null){
					$stat->setFetchMode($return_type);
				}
				$stat->execute($vals);
				$returns=NULL;
				if ($id_field==NULL){
					//Avoids returning a blank array as just fetchAll would
					foreach($stat->fetchAll() as $row){
						$returns[]=$row;
					}
					return $returns;
				} else {
					foreach($stat->fetchAll() as $row){
						$returns[$row[$id_field]]=$row;
					}
					return $returns;
				}

			} catch (PDOException $e){
				throw $e;
			}
			return false;
		}

		public function select1($info,$from,$where=NULL,$order_by=NULL,$return_type=NULL){

			try {
				$returns=$this->select($info,$from,$where,$order_by,NULL,$return_type);

				if 	(sizeof($returns[0])==2 && $return_type==NULL && $this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE)===PDO::FETCH_ASSOC ){

						return reset($returns[0]);

				} else if (sizeof($returns[0])==1 && $return_type==NULL && in_array($this->getAttribute(PDO::ATTR_DEFAULT_FETCH_MODE),Array(PDO::FETCH_ASSOC,PDO::FETCH_NUM))){

						return reset($returns[0]);

				} else {

						return $returns[0];

				}
			} catch (PDOException $e){
				throw $e;
			}
		}

		public function delete($from,$where){
			$vals=Array();
			$sql="DELETE FROM $from";
			if ($where!=""){
				if (is_array($where) && sizeof($where)>0){
					$i=0;
					$sql.=" WHERE ";					
					foreach($where as $id=>$val){
						if ($i>0){ $sql.="AND "; }
						$sql.=$id."=?";
						$vals[]=$val;
						$i++;
					}	
				} else if (is_string($where)) {
					$sql.=" WHERE $where";
				}
			}
			
			try {
				$stat=$this->prepare($sql);
				$stat->execute($vals);
				return true;
			} catch (\PDOException $e){
				throw $e;
			}
			return false;
		}		

		public function update($table,$info,$where=null){
			$vals=Array();
			$sql="UPDATE $table SET ";
			if ($info!=""){
				if (is_array($info) && sizeof($info)>0){
					$i=0;
					foreach($info as $id=>$val){
						if ($i>0){ $sql.=", "; }
						$sql.=$id."=?";
						$vals[]=$val;
						$i++;
					}	
				} else if (is_string($info)){
					$sql.=" $info";
				}
			}
			
			if ($where!=null){
				if (is_array($where) && sizeof($where)>0){
					$i=0;
					$sql.=" WHERE ";					
					foreach($where as $id=>$val){
						if ($i>0){ $sql.="AND "; }
						$sql.=$id."=?";
						$vals[]=$val;
						$i++;
					}	
				} else if (is_string($where)) {
					$sql.=" WHERE $where";
				}
			}		
			
			try {
				$stat=$this->prepare($sql);
				$stat->execute($vals);
				return true;
			} catch (PDOException $e){
				throw $e;
			}
			return false;
		}				
			
		
		public function insert($table,$info,$return_id=false,$method="INSERT"){
			$vals=Array();
			$sql=$method." INTO $table SET ";
			if ($info!=""){
				if (is_array($info) && sizeof($info)>0){
					$i=0;
					foreach($info as $id=>$val){
						if ($id==""){ 
							throw new Exception("Empty data array key. When sending data by array, all array keys must be set.");
						}
						
						if ($i>0){ $sql.=", "; }
						$sql.=$id."=?";
						$vals[]=$val;
						$i++;
					}
				} else if (is_string($info)){
					$sql.=" $info";
				}
			}

			$stat=$this->prepare($sql);
			if ($stat->execute($vals)){
				if ($return_id==false){
					return true;
				} else {
					return $this->lastInsertId();
				}
			} else {
				//$e=$stat->errorInfo();
				//trigger_error($e[2],E_USER_ERROR);
				//throw new Exception($e[2],$e[1]);
			}
			return false;
		}

		public function replace($table,$info,$return_id=false){
			return $this->insert($table,$info,$return_id,"REPLACE");
		}
		
	}
?>