<?php

namespace App\Http\Livewire\CustomerLoyality;

use Livewire\Component;
use App\Models\LoyaltyRule as LoyalityRuleModel;

class LoyalityRule extends Component
{
    public $min_amount, $max_amount;
    public $reward_type = 'points';
    public $points_type, $points_value, $lounge_visits,$points_expiry_days,$lounge_expiry_days,$effective_date;

    public $rule_id;
    public $isEdit = false;

    public function saveRule()
    {
       $this->validate([
            'min_amount'  => 'required|numeric|min:0',
            'max_amount'  => 'required|numeric|gt:min_amount',
            'reward_type' => 'required|in:points,lounge',
        
            'points_type'        => $this->reward_type === 'points' ? 'required' : 'nullable',
            'points_value'       => $this->reward_type === 'points' ? 'required|numeric|min:1' : 'nullable',
            'points_expiry_days' => $this->reward_type === 'points' ? 'required|numeric|min:1' : 'nullable',
            'lounge_visits'      => $this->reward_type === 'lounge' ? 'required|numeric|min:1' : 'nullable',
            'lounge_expiry_days' => $this->reward_type === 'lounge' ? 'required|numeric|min:1' : 'nullable',
            'effective_date'     => 'required'
            
        ]);

        $query = LoyalityRuleModel::query();

        if ($this->isEdit) {
            $query->where('id', '!=', $this->rule_id);
        }

        // Duplicate check
        if ($query->where('min_amount', $this->min_amount)
                  ->where('max_amount', $this->max_amount)
                  ->exists()) {
            $this->addError('min_amount', 'Range already exists');
            return;
        }

        $overlap = LoyalityRuleModel::where(function ($query) {
        $query->where('min_amount', '<=', $this->max_amount)
              ->where('max_amount', '>=', $this->min_amount);
            })
            ->when($this->isEdit, function ($q) {
                $q->where('id', '!=', $this->rule_id);
            })
            ->exists();
        
        if ($overlap) {
            $this->addError('min_amount', 'Range overlaps with existing rule');
            return;
        }

        // Clean data
        if ($this->reward_type == 'points') {
            $this->lounge_visits       = null;
        }

        if ($this->reward_type == 'lounge') {
            $this->points_type        = null;
            $this->points_value       = null;
        }

        $data = [
            'min_amount'    => $this->min_amount,
            'max_amount'    => $this->max_amount,
            'reward_type'   => $this->reward_type,
            'points_type'   => $this->points_type,
            'points_value'  => $this->points_value,
            'lounge_visits' => $this->lounge_visits,
            'points_expiry_days' => $this->points_expiry_days,
            'lounge_expiry_days' => $this->lounge_expiry_days,
            'effective_date' => $this->effective_date,
        ];

        if ($this->isEdit) {
            LoyalityRuleModel::where('id', $this->rule_id)->update($data);
            session()->flash('success', 'Rule updated successfully');
        } else {
            $data['status'] = 1;
            LoyalityRuleModel::create($data);
            session()->flash('success', 'Rule created successfully');
        }

        $this->resetFields();
        $this->dispatch('closeModal');
    }

    // public function resetFields()
    // {
    //     $this->reset([
    //         'min_amount','max_amount',
    //         'points_type','points_value','lounge_visits','points_expiry_days','lounge_expiry_days',
    //         'rule_id','isEdit','effective_date'
    //     ]);

    //     $this->reward_type = 'points';
    // }

    public function resetFields()
{
    $this->reset([
        'min_amount',
        'max_amount',
        'points_type',
        'points_value',
        'lounge_visits',
        'points_expiry_days',
        'lounge_expiry_days',
        'rule_id',
        'isEdit',
        'effective_date'
    ]);

    $this->reward_type = 'points';

    $this->resetValidation();
}

    public function editRule($id)
    {
        $r = LoyalityRuleModel::findOrFail($id);

        $this->rule_id = $id;
        $this->min_amount = $r->min_amount;
        $this->max_amount = $r->max_amount;
        $this->reward_type = $r->reward_type;
        $this->points_type = $r->points_type;
        $this->points_value = $r->points_value;
        $this->lounge_visits = $r->lounge_visits;
        $this->points_expiry_days = $r->points_expiry_days;
        $this->lounge_expiry_days = $r->lounge_expiry_days;
        $this->effective_date = $r->effective_date;
        $this->isEdit = true;
    }

    public function toggleStatus($id)
    {
        $r = LoyalityRuleModel::find($id);
        $r->status = !$r->status;
        $r->save();

        session()->flash('success', 'Status updated');
    }

    public function confirmDelete($id)
    {
        $this->dispatch('swal:confirmDelete', ['id' => $id]);
    }

    public function deleteRule($id)
    {
        LoyalityRuleModel::findOrFail($id)->delete();
        session()->flash('success', 'Rule deleted successfully');
    }

    public function render()
    {
        return view('livewire.customer-loyality.loyality-rule', [
            'rules' => LoyalityRuleModel::latest()->get()
        ]);
    }
}