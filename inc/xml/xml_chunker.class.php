<?php
/**
 * Class used to break up huge XML files into it's "fundamental elements" for those that are comprised of them.
 * Not all XML files are comprised of a series of fundamental elements, and nor is this even an official XML concept, 
 * but the idea is that there are some huge XML files too big for processing all at once, but that we are interested in much smaller repeated elements (generally at depth 2) that we are interested in looking at one at a time.
 * This class is meant to process such files (or streams), by dividing them into these fundamental elements and passing each one into a given callback function.
 * 
 * Note that fundamental tags don't have to be at depth 2 to be processed, but there cannot be a fundamental tag inside another or parse_file() will throw an exception
 * 
 * @example Suppose we have the the file path/truck_of_boxes.xml with the following contents:
 * <truck>
 * 		<box>
 * 			<pear></pear>
 * 			<apple></apple>
 * 		</box>
 * 		<trash>This will not be read because it isn't in the callback</trash>
 * 		<box>
 * 			<note>
 * 				<color>white</color>
 * 				<contents>This box only contains paper</contents>
 * 			</note>
 * 		</box>
 * 		<box>
 * 			<air></air>
 * 		</box>
 * </truck>
 * 
 * Then we could write the following PHP:
 * 
 * $xc = new xml_chunker('path/truck_of_boxes.xml','box','output_box');
 * function output_box($box_xml) {
 * 	echo " [".$box_xml."]/n";
 * }
 * $xc.parse_file();
 * 
 * //The above outputs the following:
 * // [<box><pear></pear><apple/></apple></box>]
 * // [<box><note><color>white</color><contents>This box only contains paper</contents></note></box>]
 * // [<box><air></air></box>]
 * 
 * @author Joshua David Conn
 * Special thanks to Richard Pickering for authorizing me to open source this! This code was originally written for him.
 */
class xml_chunker
{
	private $file_pointer; 				//stream to a huge XML file which we are reading - determned by the programmer-supplied path
	private $handler;			 		//callable function supplied externally which gets the buffer passed to it each time a fundamental element is peiced together
	private $handler_parameters;		//array of parameters to be passed to the callback function after the first
	private $fundamental_tag_name; 
	private $parser; 					//xml parser used to break up the XML file
	private $in_fundamental = false;	//boolean - whether the parser is inside a fundamental element
	private $buffer = '';				//string used to peice together a fundamental element
	private $added_entities = array();	//associative array where keys are the entity names 
	private $chunk_size = 1048576;  	//1MB - arbitrary 

	/**
	 * Creates a new XML chunker
	 *
	 * @param string $file path to the XML file that needs to be broken down
	 * @param string $fundamental_tag_name name of the elements that we want to pass to the given handler function.
	 */
	public function __construct($file, $fundamental_tag_name)
	{
		//filter input
			$this->added_entities = array();	
			if (empty($file)) 
	        { 
	        	trigger_error("empty file string used to create xml_chunker",E_USER_ERROR);
	        }
	        if (!file_exists($file)) 
	        { 
	        	trigger_error("nonexistant file used to create xml_chunker: ".$file, E_USER_ERROR);
	        }
	        if (!($this->file_pointer = fopen($file, "r"))) 
	        { 
	        	trigger_error("could not open fileused to create xml_chunker: ".$file, E_USER_ERROR);
	        }
	
	        //make sure the given tag name is valid for XML
	        try 
	        {
		        new DOMElement($fundamental_tag_name);
		    } 
		    catch(DOMException $e) 
		    {
		    	trigger_error("invalid fundamental tag name used to create xml_chunker: ".$fundamental_tag_name, E_USER_ERROR);
		    }
		//end input checking
	    
		$this->parser = xml_parser_create();
		xml_parser_set_option($this->parser, XML_OPTION_CASE_FOLDING, false);
		xml_set_element_handler($this->parser, array($this,'start_tag'), array($this,'end_tag'));
		xml_set_default_handler ($this->parser, array($this,'xml_default'));
		xml_set_character_data_handler($this->parser, array($this,'handle_char_data'));
		xml_set_external_entity_ref_handler($this->parser, array($this,'handle_external_ent'));
		$this->fundamental_tag_name=$fundamental_tag_name;
	}
	
	public function __destruct()
	{
		fclose($this->file_pointer);
	}
	
	/**
	 * Finds all fundamental elements in file specified when xml_chunker was created and passes each on to given callback
	 *
	 * @param callable $handler function that accepts XML string as first parameter.  Each time fundamental element is found, it's passed to this function.
	 * @param array $extra_parameters additional parameters that can be sent to the callback function after first parameter (which is the fundamental element) The value at index 0 will be the second argument
	 */
	public function parse_file($handler,$extra_parameters=array())
    {     
    	if(!is_callable($handler))
	    {
	    	 trigger_error("invalid handler passed to xml_chunker->parsefile(): ".$handler, E_USER_ERROR);
	    }
	    if(!is_array($extra_parameters))
	    {
	    	trigger_error("something other than array passed as extra parameters to xml_chunker->parsefile(): ".$extra_parameters, E_USER_ERROR);
	    }
	    $this->handler = $handler;
	    $this->handler_parameters = $extra_parameters; 
	    $chunk = fread($this->file_pointer, $this->chunk_size);
	    $on_first_chunk = true;
        while ($chunk)
        {
        	if(!empty($this->added_entities) && $on_first_chunk)
        	{
        		$chunk = $this->add_entities($chunk);
        	}
        	
        	$on_last_chunk = feof($this->file_pointer);
            if (!xml_parse($this->parser, $chunk, $on_last_chunk))
            {
            	// For a full list of error codes:
            	// http://www.xmlsoft.org/html/libxml-xmlerror.html#xmlParserErrors
            	$code = xml_get_error_code($this->parser);
                trigger_error('Error:'.$code.' - '.xml_error_string($code)." Line:".xml_get_current_line_number($this->parser)."\n", E_USER_ERROR);
            }
            
            $on_first_chunk=false;
            $chunk = fread($this->file_pointer, $this->chunk_size);
        }
    }
    
	/**
	 * @return the $chunk_size
	 */
	public function get_chunk_size ()
	{
		return $this->chunk_size;
	}

	/**
	 * Sets number of bytes to be read from the file at once  - default is 1MB
	 * @param int $chunk_size
	 */
	public function set_chunk_size ($chunk_size)
	{
		if(!is_whole_number($chunk_size))
		{
			trigger_error('chunk size for xml_chunker must be a whole number. value given: "'.$chunk_size.'"', E_USER_ERROR);
		}
		$this->chunk_size = $chunk_size;
	}
    
    /** 
     * Meant to deal with XML files that had undefined entities in them by forcing them to be defined
     * WARNING: This is not Currently totally reliable because it uses the add_entities function
     * In particular it only works if the original DTD is external, which it internally converts to 
     * being shared before doing the rest of the processing. Also, the DTD must be completed within the first MB of the XML file
     * 
     * @param string $entitiy_name The name of the entity to add
     * @param string $value Value of the entity to be added
     */
    public function add_entity($entitiy_name, $value)
    {
	    if (!is_utf8($value))
		{
			$value = utf8_encode($value);
		}
    	$this->added_entities[$entitiy_name]=$value;
    }
    
    
    ///////////////////////////////////////
    ////////// PRIVATE METHODS ////////////
    ///////////////////////////////////////
    
	
	private function start_tag($parser, $name, $attribs)
	{
		if($name==$this->fundamental_tag_name)
		{
			if ($this->in_fundamental)
			{
				echo "nested fundamental!";
				exit();
			}
			else
			{
				$this->in_fundamental=true;
			}
		}
		if ($this->in_fundamental)
		{
			$this->buffer .= "<".$name;
			foreach ($attribs as $attrib=>$value)
			{
				$this->buffer.= ' '.$attrib.'="'.$value.'"';
			}
			$this->buffer .= ">";
		}
	}
	
	private function end_tag($parser, $name)
	{		
		if ($this->in_fundamental)
		{
			$this->buffer .= "</".$name.">";
		}
		if($name==$this->fundamental_tag_name)
		{
			if ($this->in_fundamental)
			{
				//$this->buffer .= " - ".xml_get_current_byte_index($parser)."\n";
				$this->in_fundamental = false;
				array_unshift($this->handler_parameters, $this->buffer);
				call_user_func_array($this->handler, $this->handler_parameters);
				array_shift($this->handler_parameters);
				$this->buffer = '';
				unset($this->buffer);	
			}
			else
			{
				echo "ending to fundamental tag while not inside!";
				exit();
			}
		}
	}
	
	private function handle_char_data($parser, $data)
	{
		if ($this->in_fundamental)
		{
			$trimmed = trim($data);
			if(!empty($trimmed))
			{
				$this->buffer .= htmlspecialchars($data);
			}
		}
	}
	
	private function handle_external_ent(resource $parser, string $open_entity_names, string $base, string $system_id, string $public_id)
	{
		/* Uncomment for troubleshooting
		echo $open_entity_names."\n";
		echo $base."\n";
		echo $system_id."\n";
		echo $public_id."\n";
		exit;*/
	}
	
	private function xml_default($parser, $data)
	{
		if ($this->in_fundamental)
		{
			//see if the data matches one of the added entities	
			preg_match('#&(.*);#', $data, $matches);
			if(!empty($matches) && array_key_exists($matches[1],$this->added_entities))
			{
				$this->buffer .= $this->added_entities[$matches[1]];
			}
			else
			{
				echo 'came accross possible invalid XML:'.$data."\n";
			}
		}
	}
	
	/**
	 * Takes a chunk of XML with an external DTD and turns that into a Shared 
	 * DTD with the entities added that came through the add_entity() function.
	 * 
	 * WARNING: This could be the cause of a lot of bugs as it hasn't been verified to work with ALL valid XML
	 * 
	 * @example <!DOCTYPE e SYSTEM "a.dtd"> can become <!DOCTYPE e SYSTEM "a.dtd" [<!ENTITY> b "c"]> 
	 * 
	 * @param string $chunk the peice of XML with an External DTD
	 * @return string The input chunk with the modified version of the DTD
	 */
	private function add_entities($chunk)
	{
		//search for chunk
        $doctype_pos = strpos($chunk,'<!DOCTYPE');
        $doctype_end = strpos($chunk,'>',$doctype_pos);
        if($doctype_pos==FALSE || $doctype_end<=$doctype_pos)
        {
        	trigger_error('Could not find the DOCTYPE in initial chunk');
        }
		
		//If this already has an internal DTD (instead of shared DTD) then parsing gets much more complicated, 
		//so here for implementation practicality we just test for it and exit if it is the case
		$opening_sqr_brace = strpos($chunk,'[',$doctype_pos);
		if($opening_sqr_brace!==FALSE && $opening_sqr_brace<$doctype_end)
		{
			trigger_error('Could not add entities because the DOCTYPE format is not supported in xml_chunker - Only external doctypes currently supported', E_USER_ERROR);
		}
		$chunk_start = substr($chunk, 0, $doctype_end).'['; //beginning of chunk leading up to the character just before the closing of the doctype
		foreach ($this->added_entities as $reference=>$value)
		{
			$chunk_start.= ' <!ENTITY '.$reference.' "'.$value.'">';
		}
		$chunk_start.='] ';
		$chunk = $chunk_start.substr($chunk,$doctype_end);
		
		return $chunk;
	}
}
?>
