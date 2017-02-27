<?php
header('Content-Type: text/html; charset=UTF-8');
mb_internal_encoding("UTF-8");
mb_http_output("UTF-8");

$ldap_server = "192.168.0.1"; # LDAP Server Address
$user   = $_POST['user'];
$passwd = $_POST['passwd'];
$auth_flag=1;

print "<html><head><title>sametime photo</title>";
print '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';

print "</head><body>\n";
print '<div class="container">'."\n";
print "<h1 class=\"h1\">sametime photo</h1>\n";


if (! isset($user) ) {
	$auth_flag=0;
} elseif( isset($user) ) {

	if(trim($user) == ""){
	    print "<div class=\"alert alert-warning alert-dismissable\">";
		print "User Error: input mailaddress</div>";
		print "<A HREF=$PHP_SELF?>Retry!</A>";
		exit;
	}
	if(trim($passwd) == ""){
	    print "<div class=\"alert alert-warning alert-dismissable\">";
		print "User Error: input password</div>";
		print "<A HREF=$PHP_SELF?>Retry!</A>";
		exit;
	}
	if(! ($link_id = ldap_connect($ldap_server, 389))){
	    print "<div class=\"alert alert-warning alert-dismissable\">";
		print "Server Error: Cannot connect LDAP Server.</div>";
		exit;
	}
	if(! ldap_bind($link_id, "", "")){
	    print "<div class=\"alert alert-warning alert-dismissable\">";
		print "Server Error: Cannot bind LDAP Server.</div>";
		exit;
	}

	$sr=ldap_search($link_id, "", "mail=$user");

	if( ldap_count_entries($link_id, $sr) == 0 ){
	    print "<div class=\"alert alert-warning alert-dismissable\">";
		print "User Error: Not Authorized.</div>";
		print "<A HREF=$PHP_SELF?>Retry!</A>";
		exit;
	}
		
	$info = ldap_get_entries($link_id, $sr);
	
	$dn = $info[0]["dn"];
	if(! ldap_bind($link_id, $dn, $passwd)){
	  print "<div class=\"alert alert-warning alert-dismissable\">";
	  print "Error: auth error</div>";
	  ldap_close($link_id);
	  $passwd = "";
	  $user = "";
	  print "<A HREF=$PHP_SELF?>Retry!</A>";
	  exit;
	} else {
		
	  if( $_FILES['upload']['error'] == 0 && $_FILES['upload']['size'] < (20*1024) ) { 
	    $new["jpegphoto;binary"][0] = file_get_contents($_FILES['upload']['tmp_name']) ;
	    ldap_modify($link_id, $dn, $new);
	  }else{
	    print "<div class=\"alert alert-warning alert-dismissable\">";
	    print "<a href=\"#\" class=\"close\" data-dismiss=\"alert\" aria-label=\"close\">&times;</a>";
	    print "Image should be less than 10 kb in jpeg format</div>\n";
	  }

		$sr   = ldap_search($link_id, "", "mail=$user");
		$info = ldap_get_entries($link_id, $sr);

		$ldap_dn         = $info[0]["dn"];

		print "<table class=\"table table-bordered\">";
		print "<tr>\n";
		print "<th>DN</th><td>".$ldap_dn."</td></tr>\n";
		$ldap_jpegphoto = base64_encode($info[0]["jpegphoto;binary"][0]);
		print "<th>photo</th><td>";
		echo '<img src="data:image/jpeg;base64,'.$ldap_jpegphoto.'"/>';
		print "</td></tr>\n";
		print "</table>\n";
		print "<pre>";
		print "</pre>";
		ldap_close($link_id);
	}
} else {

}

?>
<p></p>
<hr>
<FORM ACTION="" method=POST enctype="multipart/form-data">
<TABLE class="table table-bordered">
 <TR>
  <th scope="row">Mailaddress (ex: user_name@example.com)</th>
  <TD><input type="text" name="user" size=50 ></TD>
 </TR>
 <TR>
  <th scope="row">Internet password</th>
  <TD><input type="password" name="passwd" size=20 ></TD>
 </TR>
 <TR>
  <th scope="row">photo (jpeg, less than 10kb, 72px * 72px )</th>
  <TD><input type="file" name="upload" size=20 ></TD>
 </TR>
 <TR>
  <th scope="row">　</th><TD><input type="submit" value=" update "></TD>
 </TR>
</TABLE>
</FORM>

</div></body></html>

