<?php

 require APPPATH . '/libraries/REST_controller.php';
 use Restserver\Libraries\REST_Controller;

class Info extends REST_Controller {
    /**
    * Get All Data from this method.
    *
    * @return Response
    */
    public function index_get(){
        $data=array(
            'etu'=>"etu984",
            'nom'=>"MONG SANAHARISOA",
            'prenom'=>"Adrian Jeffrey",
            'promotion'=>"P12B"
        );
        $this->response($data, REST_Controller::HTTP_OK);
    }
}