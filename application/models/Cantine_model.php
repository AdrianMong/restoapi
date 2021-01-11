<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require 'Base_model.php';

class Cantine_model extends Base_Model{
    public function __construct() {
        parent::__construct();
        date_default_timezone_set("Africa/Nairobi");
    }

    public function platDuJour(){
        $query=$this->db->query('select * from platdujour');
        $val=array();
        foreach($query->result_array() as $row){
            $val[]=$row;
        }
        return $val;
    }

    public function inscription($etu,$nom,$pwd,$dateNaissance){
        $data=array(
            'etu'=>$etu,
            'nom'=>$nom,
            'pwd'=>sha1($pwd),
            'dateNaissance'=>$dateNaissance
        );
        $this->db->insert('etudiant',$data);
    }

    public function connexion($etu,$pwd){
        $str=sprintf("select etu,nom from etudiant where etu='%s' and pwd='%s'",$etu,sha1($pwd));
        $query=$this->db->query($str);
        if($query->num_rows()!=0){
            $_SESSION['etu']=$query->row_array();
            return true;
        }
        else{
            return false;
        }
    }

    public function modifprofil($nom,$dateNaissance,$token){
        $info=$this->getInfoByToken($token);
        $data=array(
            'nom'=>$nom,
            'dateNaissance'=>$dateNaissance
        );
        $this->db->where('etu',$info['etu']);
        $this->db->update('etudiant',$data);
        $_SESSION['etu']['nom']=$nom;
    }

    public function commandePlat($codePlat,$qt,$token){
        $info=$this->getInfoByToken($token);
        $str=sprintf("select * from commandeEtudiant where etu='%s' and dateJour=current_date",$info['etu']);
        $query=$this->db->query($str);
        if($query->num_rows()!=0){
            $row=$query->row_array();
            $data=array(
                'idCommandeEtudiant'=>$row['idCommandeEtudiant'],
                'codePlat'=>$codePlat,
                'quantite'=>$qt
            );
            $this->db->insert('commandePlatEtudiant',$data);
        }
        else{
            $data=array(
                'etu'=>$info['etu'],
                'dateJour'=>date("Y-m-d")
            );
            $this->db->insert('commandeEtudiant',$data);
            $this->commandePlat($codePlat,$qt,$token);
        }
    }

    public function annulerPlat($codePlat,$token){
        $info=$this->getInfoByToken($token);
        $str=sprintf("select idCommandeEtudiant from commandeEtudiant where etu='%s' and dateJour='%s'",$info['etu'],date("Y-m-d"));
        $query=$this->db->query($str);
        $data=array(
            'actif'=>'N'
        );
        $this->db->where('idCommandeEtudiant',$query->row_array()['idCommandeEtudiant']);
        $this->db->where('codePlat',$codePlat);
        $this->db->update('commandePlatEtudiant',$data);
    }

    public function modifQt($codePlat,$qt,$token){
        $info=$this->getInfoByToken($token);
        $str=sprintf("update commandePlatEtudiant set quantite=%d where codePlat=%d and idCommandeEtudiant=(select idCommandeEtudiant from commandeEtudiant where etu='%s' and dateJour=current_date)",$qt,$codePlat,$info['etu']);
        $this->db->simple_query($str);
    }

    public function montant($token){
        $info=$this->getInfoByToken($token);
        $str=sprintf("select sum(quantite*prix) montant from Plat join (select codePlat,quantite from commandePlatEtudiant where actif='Y' and idCommandeEtudiant=(select idCommandeEtudiant from commandeEtudiant where etu='%s' and dateJour=current_date)) q1 on Plat.codePlat=q1.codePlat",$info['etu']);
        $query=$this->db->query($str);
        return $query->row_array();
    }

    public function listePlatPreparer(){
        $query=$this->db->query("select Plat.intitule,sum(quantite) quantite from commandePlatEtudiant join Plat on commandePlatEtudiant.codePlat=Plat.codePlat where idCommandeEtudiant in(select idCommandeEtudiant from commandeEtudiant where dateJour=current_date) and actif='Y' group by commandePlatEtudiant.codePlat");
        $val=array();
        foreach($query->result_array() as $row){
            $val[]=$row;
        }
        return $val;
    }

    public function platEtudiant($token){
        $info=$this->getInfoByToken($token);
        $query=$this->db->query(sprintf("select Plat.intitule,commandePlatEtudiant.* from commandePlatEtudiant join Plat on commandePlatEtudiant.codePlat=Plat.codePlat where idCommandeEtudiant in(select idcommandeEtudiant from commandeEtudiant where etu='%s' and dateJour=current_date)",$info['etu']));
        $val=array();
        foreach($query->result_array() as $row){
            $val[]=$row;
        }
        return $val;
    }



    public function insertToken($etu){
        $token=sha1($etu.date("Y-m-d")."</123456/>");
        $data=array(
            'token'=>$token,
            'dateExpiration'=>date('Y-m-d', strtotime('+ 5 days'))
        );
        $this->db->where('etu',$etu);
        $this->db->update('etudiant',$data);
        return $token;
    }

    public function connexion1($etu,$pwd){
        $str=sprintf("select etu,nom from etudiant where etu='%s' and pwd='%s'",$etu,sha1($pwd));
        $query=$this->db->query($str);
        if($query->num_rows()!=0){
            return true;
        }
        else{
            return false;
        }
    }

    public function deconnexion($token){
        $data=array(
            'token'=>null,
            'dateExpiration'=>null
        );
        $this->db->where('token',$token);
        $this->db->update('etudiant',$data);
    }

    public function verifyToken($token){
        $str=sprintf("select count(*) proof from etudiant where token='%s' and dateExpiration>=current_date",$token);
        $query=$this->db->query($str);
        return $query->row_array()["proof"];
    }

    public function getInfoByToken($token){
        $str=sprintf("select etu,nom from etudiant where token='%s'",$token);
        $query=$this->db->query($str);
        return $query->row_array();      
    }
}
?>