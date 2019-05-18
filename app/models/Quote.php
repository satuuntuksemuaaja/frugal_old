<?php

/**
 * @property mixed id
 * @property string title
 * @property mixed type
 * @property int final
 * @property mixed lead_id
 * @property mixed accepted
 * @property mixed lead
 * @property mixed meta
 */
class Quote extends Eloquent
{

    public function job()
    {
        return $this->hasOne('Job');
    }

    public function snapshots()
    {
        return $this->hasMany('Snapshot');
    }

    public function cabinets()
    {
        return $this->hasMany('QuoteCabinet');
    }

    public function lead()
    {
        return $this->belongsTo('Lead');
    }

    public function answers()
    {
        return $this->hasMany('QuoteAnswer');
    }

    public function files()
    {
        return $this->hasMany('FrugalFile');
    }

    public function granites()
    {
        return $this->hasMany('QuoteGranite');
    }

    public function addons()
    {
        return $this->hasMany('QuoteAddon');
    }

    /**
     * These are the stored brand models and size of the appliances.
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function appliances()
    {
        return $this->hasMany(QuoteAppliance::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function responsibilities()
    {
        return $this->hasMany(QuoteResponsibility::class);
    }

    /**
     * Quote Tile configurations
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tiles()
    {
        return $this->hasMany(QuoteTile::class);
    }

    public function getApplianceClass()
    {
        if ($this->appliances->count() == 0) return null;
        $class = 'text-success';
        foreach ($this->appliances as $appliance)
        {

            if (!$appliance->model || !$appliance->brand)
            {
                $class = 'text-danger';
            }
        }
        return $class;
    }

    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

}