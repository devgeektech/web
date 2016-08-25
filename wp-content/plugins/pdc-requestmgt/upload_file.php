<?php 
@ini_set('display_errors', -1);
define('WP_DEBUG', true);
$file_type= $_FILES['source_file']['type'];
$file_name= $_FILES['source_file']['name'];
$file_name_array=explode('.',$file_name);
$file_extension = $file_name_array[1];

if($file_type=="application/pdf" && $file_extension=="pdf"){
	
	$file_name_ran	=	rand(0,99999).'-'.$_FILES['source_file']['name'] ;
	if(move_uploaded_file($_FILES['source_file']['tmp_name'],'uploads/'.$file_name_ran)){
		$source_file_path=$file_name_ran;
	}else{
		$source_file_path="";
	}
	echo json_encode( array( 'source_file_path' => $source_file_path ) );

} 
if($file_type=="text/plain" && $file_extension=="txt"){

   $file_name_ran	=	rand(0,99999).'-'.$_FILES['source_file']['name'] ;
	if(move_uploaded_file($_FILES['source_file']['tmp_name'],'uploads/'.$file_name_ran)){
		$source_file_path=$file_name_ran;
	}else{
		$source_file_path="";
	}
	echo json_encode( array( 'source_file_path' => $source_file_path ) );
 
 }

if($file_type=="application/msword" && $file_extension=="doc"){
	
		 $file_name_ran	=	rand(0,99999).'-'.$_FILES['source_file']['name'] ;
		if(move_uploaded_file($_FILES['source_file']['tmp_name'],'uploads/'.$file_name_ran)){
			$source_file_path=$file_name_ran;
		}else{
			$source_file_path="";
		}
		echo json_encode( array( 'source_file_path' => $source_file_path ) );
       
	
} 
if($file_type=="application/msword" && $file_extension=="rtf"){
	
	    $file_name_ran	=	rand(0,99999).'-'.$_FILES['source_file']['name'] ;
		if(move_uploaded_file($_FILES['source_file']['tmp_name'],'uploads/'.$file_name_ran)){
			$source_file_path=$file_name_ran;
		}else{
			$source_file_path="";
		}
		echo json_encode( array( 'source_file_path' => $source_file_path ) );
 
}
 
if( $file_extension=="docx"){

		
		$file_name_ran	=	rand(0,99999).'-'.$_FILES['source_file']['name'] ;
		if(move_uploaded_file($_FILES['source_file']['tmp_name'],'uploads/'.$file_name_ran)){
			$source_file_path=$file_name_ran;
		}else{
			$source_file_path="";
		}
		echo json_encode( array( 'source_file_path' => $source_file_path ) );
 
 
	
}
if( $file_extension=="odt"){
	    

		$file_name_ran	=	rand(0,99999).'-'.$_FILES['source_file']['name'] ;
		if(move_uploaded_file($_FILES['source_file']['tmp_name'],'uploads/'.$file_name_ran)){
			$source_file_path=$file_name_ran;
		}else{
			$source_file_path="";
		}
		echo json_encode( array( 'source_file_path' => $source_file_path ) );
		
	 
	
}
?>