<?php

namespace App\Http\Livewire\Measurement;

use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Validation\Rule;
use App\Models\Measurement;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MeasurementIndex extends Component
{
    public $measurements;
    public $product_id, $short_code, $title, $status = 1, $measurementId;
    public $search = '';

    public function mount($product_id)
    {
        $this->product_id = $product_id; // Initialize with the passed product
    }

    public function rules()
    {
        return [];
    }

    public function store()
    {
        $this->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('measurements')->where(function ($query) {
                    return $query->where('product_id', $this->product_id);
                }),
            ],
            'short_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('measurements')->where(function ($query) {
                    return $query->where('product_id', $this->product_id);
                }),
            ],
        ]);

        Measurement::create([
            'product_id' => $this->product_id,
            'title' => $this->title,
            'short_code' => $this->short_code,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Measurement created successfully!');
        $this->FilterData();
        $this->resetFields();
    }

    public function edit($id)
    {
        $measurement = Measurement::findOrFail($id);
        $this->measurementId = $measurement->id;
        $this->product_id = $measurement->product_id;
        $this->title = $measurement->title;
        $this->short_code = $measurement->short_code;
        $this->status = $measurement->status;
    }

    public function update()
    {
        $this->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('measurements')->where(function ($query) {
                    return $query->where('product_id', $this->product_id);
                })->ignore($this->measurementId),
            ],
            'short_code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('measurements')->where(function ($query) {
                    return $query->where('product_id', $this->product_id);
                })->ignore($this->measurementId),
            ],
        ]);

        $measurement = Measurement::findOrFail($this->measurementId);
        $measurement->update([
            'product_id' => $this->product_id,
            'title' => $this->title,
            'short_code' => $this->short_code,
            'status' => $this->status,
        ]);

        session()->flash('success', 'Measurement updated successfully!');
        $this->FilterData();
        $this->resetFields();
    }

    public function destroy($id)
    {
        Measurement::findOrFail($id)->delete();
        $this->FilterData();
        session()->flash('success', 'Measurement deleted successfully!');
    }

    public function toggleStatus($id)
    {
        $measurement = Measurement::findOrFail($id);
        $measurement->update(['status' => !$measurement->status]);
        session()->flash('success', 'Measurement status updated successfully!');
    }

    public function resetFields()
    {
        $this->reset(['measurementId','title','short_code']);
    }

    public function updatePositions(Request $request)
    {
        try {
            $sortOrder = $request->input('sortOrder');

            if (is_string($sortOrder)) {
                $sortOrder = json_decode($sortOrder, true);
            }

            if (!is_array($sortOrder)) {
                return response()->json(['error' => 'Invalid data format'], 400);
            }

            foreach ($sortOrder as $item) {
                Measurement::where('id', $item['id'])->update(['position' => $item['position']]);
            }

            session()->flash('success', 'Positions updated successfully!');
            $this->FilterData();
        } catch (\Exception $e) {
            Log::error('Error updating positions: ' . $e->getMessage());
            return response()->json(['error' => 'Something went wrong.'], 500);
        }
    }
    public function FilterData(){
        return Measurement::where('product_id', $this->product_id)
            ->where(function ($query) {
                $query->where('title', 'like', "%{$this->search}%")
                      ->orWhere('short_code', 'like', "%{$this->search}%");
            })
            ->orderBy('position', 'asc')
            ->get();
    }

    public function render()
    {
        $this->measurements = $this->FilterData();
        $subCat = Product::select('name')->find($this->product_id);
        

        return view('livewire.measurement.measurement-index', [
            'measurements' =>  $this->measurements,
            'products' => optional($subCat)->name,
        ]);
    }
}
