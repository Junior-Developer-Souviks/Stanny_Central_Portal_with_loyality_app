<?php

namespace App\Http\Livewire\CustomerLoyality;

use Livewire\Component;
use App\Models\Setting;
use App\Models\RedemptionRule;


class ManageSettings extends Component
{
    public $welcome_bonus;
    public $point_expiry_days;
    public $rules = [];

    public function mount()
    {
        $this->welcome_bonus = Setting::where('key', 'welcome_bonus')->value('value');

        $this->point_expiry_days = Setting::where('key', 'point_expiry_days')->value('value');
        
        // REDEMPTION RULES
        $this->rules = RedemptionRule::where('status', 1)
            ->get()
            ->map(function ($item) {
                return [
                    'id' => $item->id,
                    'channel' => $item->channel,
                    'ratio' => $item->ratio,
                ];
            })->toArray();
    }

    public function updateSettings()
    {
        $this->validate([
            'welcome_bonus' => 'required|numeric|min:0',
            'point_expiry_days' => 'required|numeric|min:1',
             'rules.*.ratio' => 'required|numeric|min:0',
        ]);

        Setting::updateOrCreate(
            ['key' => 'welcome_bonus'],
            ['value' => $this->welcome_bonus]
        );

        Setting::updateOrCreate(
            ['key' => 'point_expiry_days'],
            ['value' => $this->point_expiry_days]
        );
        
          // REDEMPTION RULES UPDATE
        foreach ($this->rules as $rule) {
            RedemptionRule::where('id', $rule['id'])
                ->update([
                    'ratio' => $rule['ratio']
                ]);
        }

        session()->flash('success', 'Settings updated successfully.');
    }

    public function render()
    {
        return view('livewire.customer-loyality.manage-settings');
    }
}