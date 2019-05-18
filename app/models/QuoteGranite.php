<?php

/**
 * Short Description
 *
 * @package: fk2
 * @author: Chris Horne {chris@vocalogic.com}
 * @since: 2/7/16 - 11:14 AM
 */
class QuoteGranite extends Eloquent
{
    protected $guarded = ['id'];

    public function quote()
    {
        return $this->belongsTo('Quote');
    }

    public function granite()
    {
        return $this->belongsTo('Granite');
    }

    public function getSqftAttribute()
    {
        $measures = explode("\n", $this->measurements);
        $Cttl = 0;
        foreach ($measures AS $measure)
        {
            if (is_numeric($measure))
            $Cttl += $measure;
        }
        $Csq = ceil(($Cttl * 25.5) / 144);
        $GTTL = $Csq;
        $rbar = ($this->raised_bar_length && $this->raised_bar_depth) ? ($this->raised_bar_length * $this->raised_bar_depth) / 144 : 0;
        $rbar = ceil($rbar);
        $rbarL = ($this->raised_bar_length * 7) / 144;
        $rbarL = ceil($rbarL);
        $rbar += $rbarL;
        $GTTL += $rbar;
        $island = ceil(($this->island_length * $this->island_width) / 144);
        if ($island)
        {
            $rows[] = [null, 'Island Configuration',
                       "<a href='/quote/{$this->quote->id}/granite?granite_id=$this->id'>" . $this->island_length . " x " . $this->island_width . " = " . $island . " sq.ft" . "</a>",
                       null, null];
        }
        $GTTL += $island;
        // Take backsplash height x (total in. of countertop measurements) / 144 .. sq.ft of backsplash
        // 71 (for tom's)
        $bslash = ceil(($this->backsplash_height * $Cttl) / 144);
        $GTTL += $bslash;
       return $GTTL;
    }
}