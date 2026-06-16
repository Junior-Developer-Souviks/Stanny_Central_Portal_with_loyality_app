<?php

namespace App\Http\Livewire\CustomerLoyality;



use App\Helpers\Helper;
use App\Models\Banner;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\On;

class BannerManager extends Component
{
    use WithFileUploads;

    // Form fields
    public $title = '';
    public $image;
    public $oldImage;
    public $editingId = null;
    public $showForm = false;

   
    // Validation rules
    protected function rules()
    {
        return [
            'title' => 'required|string|max:255',
            'image' => $this->editingId
                ? 'nullable|image|max:2048'   
                : 'required|image|max:2048', 
        ];
    }

    public function openCreate()
    {
        $this->reset(['title', 'image', 'editingId','oldImage']);
        $this->showForm = true;
    }

    public function openEdit(Banner $banner)
    {
        $this->editingId = $banner->id;
        $this->title     = $banner->title;
        $this->image     = null;
        $this->showForm  = true;
        // existing image
       $this->oldImage  = $banner->image;
    }

    public function save()
    {
        $this->validate();
        $data = [
            'title'      => $this->title,
            'created_by' => Auth::guard('admin')->id(),
        ];

        $image = null;

        if ($this->image) {
            $image = Helper::handleFileUpload($this->image, 'banners');
            $data['image'] = $image;
        }


        if ($this->editingId) {

            $banner = Banner::findOrFail($this->editingId);

            // delete old image
            if ($this->image && $banner->image) {
                Storage::disk('public')->delete(
                    str_replace('storage/', '', $banner->image)
                );
            }

            $banner->update($data);

            session()->flash('success', 'Banner updated successfully.');

        } else {

            $data['display_order'] = Banner::max('display_order') + 1;
            $data['status'] = '1';
            Banner::create($data);

            session()->flash('success', 'Banner created successfully.');
        }


        $this->reset([
            'title',
            'image',
            'editingId',
            'showForm'
        ]);

        $this->dispatch('close-banner-modal');
    }

    public function toggleStatus(Banner $banner)
    {
        $banner->update([
            'status' => $banner->status == '1' ? '0' : '1',
        ]);
        session()->flash('success', 'Banner status updated.');
    }

   

   public function confirmDelete($id)
    {
        $this->dispatch('showDeleteConfirm', [
            'itemId' => $id
        ]);
    }

        public function deleteBanner($id)
    {
        $banner = Banner::findOrFail($id);

        if ($banner->image) {
            Storage::disk('public')->delete(
                str_replace('storage/', '', $banner->image)
            );
        }

        $banner->delete();

        session()->flash('success', 'Banner deleted successfully.');
    }

    public function cancel()
    {
        $this->reset(['title', 'image', 'editingId', 'showForm']);
    }

    public function render()
    {
        return view('livewire.customer-loyality.banner-manager', [
            'banners' => Banner::orderBy('display_order')->get(),
        ]);
    }

    #[On('updateBannerOrder')]
    public function updateBannerOrder($order)
    {
        foreach ($order as $item) {

            Banner::where('id', $item['id'])
                ->update([
                    'display_order' => $item['position']
                ]);

        }

        session()->flash('success','Order updated successfully');
    }
}
