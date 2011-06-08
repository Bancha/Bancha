<?php 
//TODO remove hardcorded values

//echo json_encode($this->viewVars['API']);

//echo Configure::read("Bancha.namespace"); 
echo "Ext.ns('Ext.app');\n" ;

//echo Configure::read("Bancha.remoteAPI") . ' = ' . json_encode($API);
echo "Ext.app.REMOTING_API = ";
echo json_encode($API);

?>

