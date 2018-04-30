<?php
/*
 * 2015/06/02
 * class for Dynamic Time Warping
 *
 * AUTHOR
 * Aki Kunikoshi
 * 428968@gmail.com
 */
 
class c_dtw
{
    public $sentenceA = '';
    public $sentenceB = '';
    
    public $sentenceArrayA = array();
    public $sentenceArrayB = array();

    public $wordNumA = 0;
    public $wordNumB = 0;
    
    public $dMat = array();

    public function set_variables($sentenceA, $sentenceB)
    {
	$this->sentenceA = $sentenceA;
	$this->sentenceB = $sentenceB;
        
        // ====================
        // sentences will be seperated
        // ====================
        $this->sentenceArrayA = preg_split("/[\s]+/", $this->sentenceA);
        $this->sentenceArrayB = preg_split("/[\s]+/", $this->sentenceB);

        $this->wordNumA = count($this->sentenceArrayA);
        $this->wordNumB = count($this->sentenceArrayB);
    }
    
    public function dtw()
    {
        //$this->wordA_ = $this->sentenceArrayA[$a];
        //$this->wordB_ = $this->sentenceArrayB[$b];
        
        // distance matrix
        for($r = 0; $r < $this->wordNumA; $r++)
        {
            for($c = 0; $c < $this->wordNumB; $c++)
            {            
                $this->dMat[$r][$c] = levenshtein($this->sentenceArrayA[$r], $this->sentenceArrayB[$c]);
            } // $c 
        } // $r
    }
    
    public function dispDmat()
    {
        for($r = 0; $r < $this->wordNumA; $r++)
        {
            for($c = 0; $c < $this->wordNumB; $c++)
            {
                print ' (' . $r . ', ' . $c . ') = ' . $this->dMat[$r][$c];
            }
            echo "</br>";
        }
    }
}
?>