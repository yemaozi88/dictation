<?php

/* 
 * copied from: https://qiita.com/yorozuone/items/b4a1a16fc021d15e6fb7
 */


class WavControl
{
    var $_d = '';   // data

    var $datasize   = 0;
    var $fmtid  = 0;    // format ID
    var $chsize = 0;    // channel size
    var $freq   = 0;    // sampling frequency

    
    function LoadFile($fn)
    {
        $this->_d = file_get_contents($fn);

        // check if the file is wav (=first 4 bite should be 'RIFF')
        if (substr($this->_d, 0, 4) != 'RIFF') {
            return false;
        }

        // chenk code WAVE exists.
        if (substr($this->_d, 8, 4) != 'WAVE') {
            return false;
        }

        // chenk code fmt.
        if (substr($this->_d, 12, 4) != 'fmt ') {
            return false;
        }

        // chunk code data.
        if (substr($this->_d, 36, 4) != 'data') {
            return false;
        }

        // get format ID.
        // only concern linear PCM. Otherwise error.
        $d = unpack('v', substr($this->_d, 20, 2));
        $this->fmtid = $d[1];

        if ($this->fmtid != 1) {
            return false;
        }

        // get channel number.
        // only concern monoral.
        $d = unpack('v', substr($this->_d, 22, 2));
        $this->chsize = $d[1];

        if ($this->fmtid != 1) {
            return false;
        }

        // get sampling frequency.
        // only concern 44100hz.
        $d = unpack('V', substr($this->_d, 24, 4));
        $this->freq = $d[1];

        // get data size.
        $d = unpack('V', substr($this->_d, 40, 4));
        $this->datasize = $d[1];
    }

    
    function SaveFile($p1) 
    {
        file_put_contents($p1, $this->_d);

    }
    
    
    function WaveConnect(&$p1) 
    {
        // connect only data part of WAVE files.
        $this->_d = $this->_d . substr($p1->_d, 44, $p1->datasize);

        // change data size.
        $this->datasize = strlen($this->_d) - 44;

        // change data size in real.
        $d = pack('V', strlen($this->_d) - 8);
        $this->_d[4] = $d[0];
        $this->_d[5] = $d[1];
        $this->_d[6] = $d[2];
        $this->_d[7] = $d[3];

        $d = pack('V', $this->datasize);
        $this->_d[40] = $d[0];
        $this->_d[41] = $d[1];
        $this->_d[42] = $d[2];
        $this->_d[43] = $d[3];

    }
}


function wavConcatenate($wav_dir, $wavs, $wav_out)
{
    $wavNumMax = count($wavs);
    if($wavNumMax == 0)
    {
        exit("wav file is not given.\n");
    }
    else
    {
        $w0 = new WavControl();
        $w0->LoadFile($wav_dir . '/' . $wavs[0]);

        if ($wavNumMax > 1)
        {
            for($i=1; $i<$wavNumMax; $i++)
            {
                $w_ = new WavControl();
                $w_->LoadFile($wav_dir . '/' . $wavs[$i]);
                $w0->WaveConnect($w_);
                unset($w_);
            }
        }

        $w0->SaveFile($wav_out);
    }
}
?>