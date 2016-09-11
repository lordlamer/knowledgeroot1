<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of db_core_interface
 *
 * @author fhabermann
 */
interface db_core_interface {
    public function connect($host,$user,$pass,$db,$schema="",$encoding="");
    public function close();
    public function query($query);
}
