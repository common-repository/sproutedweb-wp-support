<?php
/**
 * CodexWorld
 *
 * This Pagination class helps to integrate pagination in PHP.
 *
 * @class      Pagination
 * @author     CodexWorld
 * @link       http://www.codexworld.com
 * @license    http://www.codexworld.com/license
 * @version    2.0
 */
class Pagination{
    protected $baseURL        = '';
    protected $totalRows      = '';
    protected $perPage        = 10;
    protected $numLinks       = 2;
    protected $currentPage    =  0;
    protected $ajax     	 = 1;
    protected $ajaxBase     	 = 'javascript:void(0);';
    protected $firstLink      = 'First';
    protected $nextLink       = 'Next &raquo;';
    protected $prevLink       = '&laquo; Prev';
    protected $lastLink       = 'Last';
    protected $fullTagOpen    = '<div class="pagination2 pull-right">';
    protected $fullTagClose   = '</div>';
    protected $firstTagOpen   = '';
    protected $firstTagClose  = '&nbsp;';
    protected $lastTagOpen    = '&nbsp;';
    protected $lastTagClose    = '';
    protected $curTagOpen    = '&nbsp;<b>';
    protected $curTagClose    = '</b>';
    protected $nextTagOpen    = '&nbsp;';
    protected $nextTagClose    = '&nbsp;';
    protected $prevTagOpen    = '&nbsp;';
    protected $prevTagClose    = '';
    protected $numTagOpen    = '&nbsp;';
    protected $numTagClose    = '';
    protected $showCount    = true;
    protected $currentOffset= 0;
    protected $queryStringSegment = 'page_no';
    
    function __construct($params = array()){
        if (count($params) > 0){
            $this->initialize($params);        
        }
    }
    
    function initialize($params = array()){
        if (count($params) > 0){
            foreach ($params as $key => $val){
                if (isset($this->$key)){
                    $this->$key = $val;
                }
            }        
        }
    }
    
    /**
     * Generate the pagination links
     */    
    function createLinks(){ 
        // If total number of rows is zero, do not need to continue
        if ($this->totalRows == 0 OR $this->perPage == 0){
           return '';
        }
        // Calculate the total number of pages
        $numPages = ceil($this->totalRows / $this->perPage);
        // Is there only one page? will not need to continue
        if ($numPages == 1){
            if ($this->showCount){
                $info = $this->fullTagOpen.'Showing : 1 To ' . $this->totalRows.$this->fullTagClose;
                return $info;
            }else{
                return '';
            }
        }
        
        // Determine query string
        $query_string_sep = (strpos($this->baseURL, '?') === FALSE) ? '?page_no=' : '&amp;page_no=';
        $this->baseURL = $this->baseURL.$query_string_sep;
        
        // Determine the current page
        $this->currentPage = isset($_REQUEST[$this->queryStringSegment])?$_REQUEST[$this->queryStringSegment]:0;
        
        if (!is_numeric($this->currentPage) || $this->currentPage == 0){
            $this->currentPage = 1;
        }
        
        // Links content string variable
        $output = '';
        
        // Showing links notification
        if ($this->showCount){
           $currentOffset = ($this->currentPage > 1)?($this->currentPage - 1)*$this->perPage:$this->currentPage;
           $info = 'Showing ' . $currentOffset . ' to ' ;
        
           if( ($currentOffset + $this->perPage) <= $this->totalRows )
              $info .= $this->currentPage * $this->perPage;
           else
              $info .= $this->totalRows;
        
           $info .= ' of ' . $this->totalRows . ' | ';
        
           $output .= $info;
        }
        
        $this->numLinks = (int)$this->numLinks;
        
        // Is the page number beyond the result range? the last page will show
        if($this->currentPage > $this->totalRows){
            $this->currentPage = $numPages;
        }
        
        $uriPageNum = $this->currentPage;
        
        // Calculate the start and end numbers. 
        $start = (($this->currentPage - $this->numLinks) > 0) ? $this->currentPage - ($this->numLinks - 1) : 1;
        $end   = (($this->currentPage + $this->numLinks) < $numPages) ? $this->currentPage + $this->numLinks : $numPages;
        
        // Render the "First" link
        if($this->currentPage > $this->numLinks){
            $firstPageURL = str_replace($query_string_sep,'',$this->baseURL);
            $output .= $this->firstTagOpen.'<a href="'.($this->ajax ? $this->ajaxBase : $firstPageURL).'" data-page="1">'.$ajax.$this->firstLink.'</a>'.$this->firstTagClose;
        }
        // Render the "previous" link
        if($this->currentPage != 1){
            $i = ($uriPageNum - 1);
            if($i == 0) $i = '';
            $output .= $this->prevTagOpen.'<a href="'.($this->ajax ? $this->ajaxBase : $this->baseURL.$i).'" data-page="'.$i.'">'.$this->prevLink.'</a>'.$this->prevTagClose;
        }
        // Write the digit links
        for($loop = $start -1; $loop <= $end; $loop++){
            $i = $loop;
            if($i >= 1){
                if($this->currentPage == $loop){
                    $output .= $this->curTagOpen.$loop.$this->curTagClose;
                }else{
                    $output .= $this->numTagOpen.'<a href="'.($this->ajax ? $this->ajaxBase : $this->baseURL.$i).'" data-page="'.$loop.'">'.$loop.'</a>'.$this->numTagClose;
                }
            }
        }
        // Render the "next" link
        if($this->currentPage < $numPages){
            $i = ($this->currentPage + 1);
            $output .= $this->nextTagOpen.'<a href="'.($this->ajax ? $this->ajaxBase : $this->baseURL.$i).'" data-page="'.$i.'">'.$this->nextLink.'</a>'.$this->nextTagClose;
        }
        // Render the "Last" link
        if(($this->currentPage + $this->numLinks) < $numPages){
            $i = $numPages;
            $output .= $this->lastTagOpen.'<a href="'.($this->ajax ? $this->ajaxBase : $this->baseURL.$i).'" data-page="'.$i.'">'.$this->lastLink.'</a>'.$this->lastTagClose;
        }
        // Remove double slashes
        $output = preg_replace("#([^:])//+#", "\\1/", $output);
        // Add the wrapper HTML if exists
        $output = $this->fullTagOpen.$output.$this->fullTagClose;
        
        return $output;        
    }
}