<?php
$id=$_GET['q'];
$barcode_prefix_regexp = '/^'.preg_replace('/\//', '\/', BARCODE_PREFIX).'/';
if(preg_match($barcode_prefix_regexp, $id)) $id=preg_replace($barcode_prefix_regexp, '', $id);
if(preg_match('/\//', $id)) $this->post_redirect_get($id);
	else $this->post_redirect_get('?q='.urlencode($id));
