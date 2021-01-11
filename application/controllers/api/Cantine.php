<?php

 require APPPATH . '/libraries/REST_Controller.php';
 use Restserver\Libraries\REST_Controller;

class Cantine extends REST_Controller {

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function __construct() {
        parent::__construct();
        $this->load->model('cantine_model');
    }


    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function index_get() 
    {
        try{
            $data=$this->cantine_model->platDuJour();
            $status=array(
                "code"=>200
            );
        }
        catch(Exception $e){
            $data=[];
            $status=array(
                "code"=>500,
                "message"=>$e->getMessage()
            );

        }
        $response=array(
            "status"=>$status,
            "data"=>$data
        );
        $this->response($response, REST_Controller::HTTP_OK);
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function inscription_post(){
        $this->cantine_model->inscription($this->input->post("etu"),$this->input->post("nom"),$this->input->post("pwd"),$this->input->post("dateNaissance"));
        $data=array(
            "status"=>200,
            "message"=>'Etudiant cree avec succes.'
        );
        $this->response($data, REST_Controller::HTTP_OK);
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function connexion_post(){
        $etu=$this->input->post("etu");
        $pwd=$this->input->post("pwd");
        if($this->cantine_model->connexion1($etu,$pwd)){
            $token=$this->cantine_model->insertToken($etu);
            $info=$this->cantine_model->getInfoByToken($token);
            setcookie("token",$token);
            $this->response([sprintf('Connecte(e) en tant que %s.',$info["nom"])],REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez verifier vos informations de connexion.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function deconnexion_get(){
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->cantine_model->deconnexion($token);
            delete_cookie("token");
            $this->response(['Deconnexion effectuee.'],REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function modifprofil_put(){
        $nom=$this->put('nom');
        $dateNaissance=$this->put('dateNaissance');
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->cantine_model->modifProfil($nom,$dateNaissance,$token);
            $this->response(['Modification effectuee.'],REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function commandePlat_post(){
        $codePlat=$this->input->post("codePlat");
        $qt=$this->input->post("qt");
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->cantine_model->commandePlat($codePlat,$qt,$token);
            $this->response(['Commande effectuee avec succes.'],REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function annulerPlat_put(){
        $codePlat=$this->put("codePlat");
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->cantine_model->annulerPlat($codePlat,$token);
            $this->response(['Commande du plat annulee.'],REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function modifQt_put(){
        $codePlat=$this->put("codePlat");
        $qt=$this->put("qt");
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->cantine_model->modifQt($codePlat,$qt,$token);
            $this->response(['Commande modifiee.'],REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function montant_get(){
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->response($this->cantine_model->montant($token),REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function listePlatPreparer_get(){
        $this->response($this->cantine_model->listePlatPreparer(), REST_Controller::HTTP_OK);
    }

    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function listePlatEtudiant_get(){
        $token=get_cookie("token");
        if($this->cantine_model->verifyToken($token)==1){
            $this->response($this->cantine_model->platEtudiant($token),REST_Controller::HTTP_OK);
        }
        else{
            $this->response(['Veuillez vous connecter.'],REST_Controller::HTTP_OK);
        }
    }
}