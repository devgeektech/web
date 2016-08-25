<?php 
@ini_set('display_errors', -1);
define('WP_DEBUG', true);
$file_type= $_FILES['file']['type'];
$file_name= $_FILES['file']['name'];
$file_name_array=explode('.',$file_name);
$file_extension = $file_name_array[1];

if($file_type=="application/pdf" && $file_extension=="pdf"){
	//include('pdf2text.php');

	include ('pdfparser-master/vendor/autoload.php');

	$parser = new \Smalot\PdfParser\Parser();
	$pdf    = $parser->parseFile($_FILES['file']['tmp_name']);
 
	// Retrieve all pages from the pdf file.
	$pages  = $pdf->getPages();
	 
	// Loop over each page to extract text.
	$text="";
	foreach ($pages as $page) {
		$text.= $page->getText();
	}
	
 
	/* $details  = $pdf->getDetails();
	
	foreach ($details as $property => $value) {
		if (is_array($value)) {
			$value = implode(', ', $value);
		}
		$text.= $property . ' => ' . $value . "\n";
    } */
	 
	echo json_encode( array( 'result' => $text) );

} 
if($file_type=="text/plain" && $file_extension=="txt"){

$fh = fopen($_FILES['file']['tmp_name'],'r');
$file_content="";
while ($line = fgets($fh)) {
	$file_content.= $line;

}
fclose($fh);
echo json_encode( array( 'result' =>$file_content) );
 }

if($file_type=="application/msword" && $file_extension=="doc"){
	 $fileHandle = fopen($_FILES['file']['tmp_name'], "r");
        $line = @fread($fileHandle, filesize($_FILES['file']['tmp_name']));   
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
         $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
       echo json_encode( array( 'result' =>$outtext) );
	
} 
if($file_type=="application/msword" && $file_extension=="rtf"){
	include('rtfclass.php');
	$outtext= rtf2text($_FILES['file']['tmp_name']);
	echo json_encode( array( 'result' =>$outtext) );
	// $r = new rtf( stripslashes( 'sample.rtf' ));
		// $r->output( "xml");
		// $r->parse();
		// if( count( $r->err) == 0) // no errors detected
			// echo $r->out;
	
}
 
if( $file_extension=="docx"){


	//echo $_FILES['file']['tmp_name'];
	$striped_content = '';
        $content = '';

       $zip = zip_open($_FILES['file']['tmp_name']);
       // echo  $zip = zip_open('/var/www/vhosts/setlr.com/test.setlr.com/wp-content/themes/setlr/sample.docx');

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);       
		echo json_encode( array( 'result' => trim($striped_content)) );
	
}
if( $file_extension=="odt"){
	    
		
        function readZippedXML($archiveFile, $dataFile) {
			// Create new ZIP archive
			$zip = new ZipArchive;

			// Open received archive file
			if (true === $zip->open($archiveFile)) {
				// If done, search for the data file in the archive
				if (($index = $zip->locateName($dataFile)) !== false) {
					// If found, read it to the string
					$data = $zip->getFromIndex($index);
					// Close archive file
					$zip->close();
					// Load XML from a string
					$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $data);
					$content = str_replace('</text:span>', "\r\n", $content);
					$striped_content = strip_tags($content); 
					return $striped_content;
				}
				$zip->close();
			}

			// In case of failure return empty string
			return "";
		}
		$striped_content= readZippedXML($_FILES['file']['tmp_name'], "content.xml");	
		echo json_encode( array( 'result' => trim($striped_content)) );	
	
}
 if(1==2) {
	
  class DocxConversion{
    private $filename;

    public function __construct($filePath) {
        $this->filename = $filePath;
    }

    private function read_doc() {
        $fileHandle = fopen($this->filename, "r");
        $line = @fread($fileHandle, filesize($this->filename));   
        $lines = explode(chr(0x0D),$line);
        $outtext = "";
        foreach($lines as $thisline)
          {
            $pos = strpos($thisline, chr(0x00));
            if (($pos !== FALSE)||(strlen($thisline)==0))
              {
              } else {
                $outtext .= $thisline." ";
              }
          }
         $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext);
        return $outtext;
    }

    private function read_docx(){

        $striped_content = '';
        $content = '';

        $zip = zip_open($this->filename);

        if (!$zip || is_numeric($zip)) return false;

        while ($zip_entry = zip_read($zip)) {

            if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

            if (zip_entry_name($zip_entry) != "word/document.xml") continue;

            $content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

            zip_entry_close($zip_entry);
        }// end while

        zip_close($zip);

        $content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
        $content = str_replace('</w:r></w:p>', "\r\n", $content);
        $striped_content = strip_tags($content);

        return $striped_content;
    }



function xlsx_to_text($input_file){
    $xml_filename = "xl/sharedStrings.xml"; //content file name
    $zip_handle = new ZipArchive;
    $output_text = "";
    if(true === $zip_handle->open($input_file)){
        if(($xml_index = $zip_handle->locateName($xml_filename)) !== false){
            $xml_datas = $zip_handle->getFromIndex($xml_index);
            $xml_handle = DOMDocument::loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            $output_text = strip_tags($xml_handle->saveXML());
        }else{
            $output_text .="";
        }
        $zip_handle->close();
    }else{
    $output_text .="";
    }
    return $output_text;
}

function pptx_to_text($input_file){
    $zip_handle = new ZipArchive;
    $output_text = "";
    if(true === $zip_handle->open($input_file)){
        $slide_number = 1; //loop through slide files
        while(($xml_index = $zip_handle->locateName("ppt/slides/slide".$slide_number.".xml")) !== false){
            $xml_datas = $zip_handle->getFromIndex($xml_index);
            $xml_handle = DOMDocument::loadXML($xml_datas, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
            $output_text .= strip_tags($xml_handle->saveXML());
            $slide_number++;
        }
        if($slide_number == 1){
            $output_text .="";
        }
        $zip_handle->close();
    }else{
    $output_text .="";
    }
    return $output_text;
}


    public function convertToText() {
		echo "hello";
        if(isset($this->filename) && !file_exists($this->filename)) {
            return "File Not exists";
        }

        $fileArray = pathinfo($this->filename);
       echo $file_ext  = $fileArray['extension'];
        if($file_ext == "doc" || $file_ext == "docx" || $file_ext == "xlsx" || $file_ext == "pptx")
        {
            if($file_ext == "doc") {
                return $this->read_doc();
            } elseif($file_ext == "docx") {
                return $this->read_docx();
            } elseif($file_ext == "xlsx") {
                return $this->xlsx_to_text();
            }elseif($file_ext == "pptx") {
                return $this->pptx_to_text();
            }
        } else {
            return "Invalid File Type";
        }
    }

}	
	
echo "done";
echo dirname(__FILE__);
print_r($_FILES);
// $docObj = new DocxConversion('/var/www/vhosts/setlr.com/test.setlr.com/wp-content/themes/setlr/sample.docx');
$docObj = new DocxConversion($_FILES['file']['tmp_name']);
//$docObj = new DocxConversion("test.docx");
//$docObj = new DocxConversion("test.xlsx");
//$docObj = new DocxConversion("test.pptx");
echo $docText= $docObj->convertToText();


	
} 

?>