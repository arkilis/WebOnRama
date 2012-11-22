<html>
<title>SAFS</title>
<script language="javascript">

function add_input_file(tid, index) 
{ 
	file_name = "md5_file" + index; 
	var tr_new = document.createElement("tr"); 
	var td_new1 = document.createElement("td"); 
	var td_new2= document.createElement("td"); 
	var td_new3 = document.createElement("td"); 
	var input_file = document.createElement("input");
	input_file.setAttribute("type","text"); 
	input_file.setAttribute("name",file_name);
	input_file.setAttribute("id",file_name);
	input_file.setAttribute("style","background-color:#F5AB00; width:500px");
	

	td_new2.appendChild(input_file); 
	tr_new.appendChild(td_new1); 
	tr_new.appendChild(td_new2); 
	tr_new.appendChild(td_new3); 
	tid.appendChild(tr_new); 
} 

function add_one_file() 
{ 
	
	var tId = document.getElementById("tMd5");
	var tCount = document.getElementById("tMd5").childNodes.length; 
	document.getElementById("md5Counter").value=tCount;
	add_input_file(tId, tCount); 
}


// ajax

function get_XmlHttp() 
{
	// create the variable that will contain the instance of the XMLHttpRequest object (initially with null value)
	var xmlHttp = null;

	if(window.XMLHttpRequest) {		// for Forefox, IE7+, Opera, Safari, ...
		xmlHttp = new XMLHttpRequest();
	}
	else if(window.ActiveXObject) {	// for Internet Explorer 5 or 6
		xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
	}

	return xmlHttp;
}

// sends data to a php file, via POST, and displays the received answer to tagID
function ajaxrequest(php_file, tagID) 
{
	// call the function for the XMLHttpRequest instance
	var request =  get_XmlHttp();		

	// create pairs index=value with data that must be sent to server
	// if you want to add more, just create more pairs, using '&' to add together
	//var  the_data = 'test='+document.getElementById('md5_file1').innerHTML;
	var data='md5Counter='+document.getElementById('md5Counter').value+"&";
	for(var i=1;i<=document.getElementById("md5Counter").value;i++)
	{
		itmp=i+"";
		tmp="md5_file"+itmp+"="+document.getElementById('md5_file'+itmp).value;
		data+=tmp;
		data+="&";
		//debug
		//window.console(document.getElementsByName('md5_file'+itmp).value);
	}
	
	// set the request
	request.open("POST", php_file, true);			
	// adds  a header to tell the PHP script to recognize the data as is sent via POST
	request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	request.send(data);		// calls the send() method with datas as parameter
	
	// show loading
	document.getElementById(tagID).innerHTML = "<img src='images/loading.gif' alt='Loading' />";
	
	// Check request status
	// If the response is received completely, will be transferred to the HTML tag with tagID
	request.onreadystatechange = function() {
	
	
	
    if (request.readyState == 4 && request.status==200 ) {
      document.getElementById(tagID).innerHTML = request.responseText;
    }
  }
}

</script>
<body>
<!--
The purpose of this page:
1, generate user.csv
2, insert mysql database, as the 1st step as running turbometadb before
3, generate user' md5 signature
4, generate .code

-->
<?php

include "conn.php";

// connect to the database
$conn= getConn();
?>


<h2>File checking</h2>
<hr />
<p>Notice: Please put your data files under the shared drive. e.g. 20121121/201211211348/</p>
<?php

include "common.php";

$path="/home/miseq01/rawData/".getCurDate();
show_list($path);
$ay_ext=array("fastq", "gz", "md5", "csv", "code");
checkFiles($ay_files);

//debug
//echo(getReadLen($ay_files, "/tmp"));

// Total files
echo("<br />Total: ".sizeof($ay_files)." files.");

//execute
$ay_fq=getFafiles($ay_files);


//debug
//print_r($ay_fq);

?>
<br />
<!--**************************************** Building your MD5 *******************************************************-->
<h2>Building your MD5</h2>
<hr />

<p>File should be the final data file uploaded to Barrine.</p>
<form action="recvMD5.php" method="POST" id="frmMD5">
<table id="tMd5">
<tr>
<td style="width: 143px;">File: </td>
<td><input style="background-color:#F5AB00; width:500px" type="text" name="md5_file1" id="md5_file1"/></td>
<td><input type="button" value="Add another" onclick="add_one_file()" /></td>
<td><input type="hidden" id="md5Counter" name="md5Counter" value="1" /></td>
</tr>

</table>
<input type="submit" value="Gen md51" />
<input type="button" value="Gen md52" onclick="ajaxrequest('recvMD5.php','stat_md5');" />
</form>
<p id="stat_md5"></p>
<br />
<!--**************************************** Building your CSV *******************************************************-->
<h2>Building your user.csv</h2>
<hr />
<form action="gen.php" method="POST">
<table>
<tr>
<td>Lane Number:</td>
<td><input type="text" name="lane_number"/></td>
<td></td>
</tr>
<tr>
<td>Number of Library:</td>
<td><input type="text" name="numLib" value="1" ></input></td>
<td><font color="red">Normally it is 1 by default. Refers to wiki.</font></td>
</tr>
<tr>
<td>Biosource_library_key:</td>
<td><input type="text" name="biolibkey" value="ABC" ></input>*</td>
<td></td>
</tr>
<tr>
<!--
<td>Biosource_id:</td>
<td><input type="text" name="lane_number" value="51" ></input>* Read Only</td>
</tr>
-->
<tr>
<td>Biosource (cultiva):</td>
<td><select name='bName'>
<!-- hard code here, will be changed later-->
	<?php
		$res= mysql_query("select cname from cultiva");
		while($row=mysql_fetch_array($res, MYSQL_BOTH))
		{
			echo("<option value='".$row["cname"]."'>".$row["cname"]."</option>");
		}
	?>
</select></td>
<td>
<input type="button" onclick="javascript:window.open('newCultiva.php');" value="Add a new cultiva" />
</td>
</tr>
<!-- make species id hidden
<tr>
<td>Species_id:</td>
<td><input type="text" name="lane_number" value="6" readonly ></input>* Read only</td>
</tr>
-->
<tr>
<td>Species:</td>
<td><select name='sName'>
<!-- hard code here, will be changed later-->
	<?php
		$res= mysql_query("select sname from species");
		while($row=mysql_fetch_array($res, MYSQL_BOTH))
		{
			echo("<option value='".$row["sname"]."'>".$row["sname"]."</option>");
		}
	?>
</select></td>
<td><input type="button" onclick="javascript:window.open('newSpecies.php');" value="Add a new species" /></td>
</tr>
<tr>
<td>Read_length:</td>
<td><input style="background-color:#F5AB00" type="text" name="readLen" readonly value="<?php echo(getReadLen($ay_files, "/tmp")); ?>" >
</input>*</td>
<td><font color="red">Automatically read from data file.</font></td>
</tr>
<tr>
<td>Insert_size:</td>
<td><input type="text" name="insert_size" value="300" ></input>*</td>
<td></td>
</tr>
<tr>
<td>File A path:</td>
<td><input style="background-color:#F5AB00; width:500px" type="text"  name="fileA"
value="<?php echo($ay_fq[0]); ?>"></input>*</td>
<td>Check the right name of fastq file</td>
</tr>
<tr>
<td>File B path:</td>
<td><input style="background-color:#F5AB00; width:500px" type="text"  name="fileB"
value="<?php echo($ay_fq[1]); ?>"></input>*</td>
<td>Check the right name of fastq file</td>
</tr>
<tr>
<td></td>
<td><input type="submit" value="OK" ></input></td>
<td></td>
</tr>
</table>
</form>
<hr />

<!--
<h2>Encode cultivar name</h2>
<hr />
-->
</body>

</html>
