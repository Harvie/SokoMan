<?php
if(!isset($_GET['q'])) $this->post_redirect_get('',"This assistant shouldn't be used like this");
$id=$_GET['q'];
$barcode_prefix_regexp = '/^'.preg_replace('/\//', '\/', BARCODE_PREFIX).'/';
if(preg_match($barcode_prefix_regexp, $id)) $id=preg_replace($barcode_prefix_regexp, '', $id);
if(preg_match('/\//', $id)) $this->post_redirect_get(strtolower($id));
	else $this->post_redirect_get('?q='.urlencode($id));
