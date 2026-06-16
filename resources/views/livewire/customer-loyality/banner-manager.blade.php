<div class="container">
    <section class="admin__title mb-5">
        <h5>Banner Management</h5>
    </section>

    <section>
        <div class="search__filter">
            <div class="row align-items-center justify-content-end">
                {{-- <div class="col-auto">
                    <div class="row g-3 align-items-center">
                        <div class="col-auto mt-0">
                            <input type="text" wire:model.live="search" class="form-control select-md bg-white"
                                placeholder="Search Banners" style="width: 350px;">
                        </div>
                        <div class="col-auto mt-3">
                            <button type="button" wire:click="cancel"
                                class="btn btn-outline-danger select-md">Clear</button>
                        </div>
                    </div>
                </div> --}}
                <div class="col-auto">
                    <button type="button" class="btn btn-outline-success select-md"
                        data-bs-toggle="modal" data-bs-target="#bannerModal"
                        wire:click="openCreate">
                        Add Banner
                    </button>
                </div>
            </div>
        </div>
    </section>

    {{-- Add / Edit Modal --}}
    <div wire:ignore.self class="modal fade" id="bannerModal" tabindex="-1"
        aria-labelledby="bannerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="bannerModalLabel">
                        {{ $editingId ? 'Edit Banner' : 'Add Banner' }}
                    </h5>
                    <button type="button" class="btn btn-outline-danger custom-btn-sm"
                        data-bs-dismiss="modal" wire:click="cancel">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
                <div class="modal-body">

                   

                    <form wire:submit.prevent="save" enctype="multipart/form-data">

                        <div class="mb-3">
                            <label class="form-label">Title</label>
                            <input type="text" wire:model="title"
                                class="form-control" placeholder="Enter banner title">
                            @error('title')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Banner Image
                              
                            </label>
                            <input type="file" wire:model="image"
                                class="form-control" accept="image/*">
                            @error('image')
                                <span class="text-danger text-xs">{{ $message }}</span>
                            @enderror

                            {{-- New image preview --}}
                            @if ($image)
                                <img src="{{ $image->temporaryUrl() }}"
                                    class="mt-2 rounded"
                                    style="height:80px; object-fit:cover;">
                            @endif


                            {{-- Old image preview --}}
                            @if (!$image && $oldImage)
                                <img src="{{ asset($oldImage) }}"
                                    class="mt-2 rounded"
                                    style="height:80px; object-fit:cover;">
                            @endif
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit"
                                wire:loading.attr="disabled"
                                class="btn btn-outline-success select-md">
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingId ? 'Update Banner' : 'Create Banner' }}
                                </span>
                                <span wire:loading wire:target="save">Saving...</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

     
    {{-- Table --}}
    <div class="row">
        <div class="col-12">
            @if (session()->has('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if (session()->has('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
            <div class="card my-4">
                <div class="card-body pb-0">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center mb-0">

                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Image
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Title
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Status
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Created By
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10">
                                        Created At
                                    </th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-10 text-center">
                                        Action
                                    </th>
                                </tr>
                            </thead>

                            <tbody id="bannerTableBody">
                                @forelse($banners as $key => $banner)
                                <tr 
                                    wire:key="banner-{{ $banner->id }}"
                                    data-id="{{ $banner->id }}"
                                    style="cursor: move;"
                                >


                                    {{-- Image --}}
                                    <td>
                                        <img src="{{ asset($banner->image) }}"
                                            alt="{{ $banner->title }}"
                                            width="80px" style="object-fit: cover; border-radius: 4px;">
                                    </td>

                                    {{-- Title --}}
                                    <td>
                                        <h6 class="mb-0 text-sm">{{ $banner->title }}</h6>
                                    </td>

                                    {{-- Status toggle --}}
                                    <td>
                                        <div class="form-check form-switch">
                                            <input class="form-check-input ms-auto" type="checkbox"
                                                wire:click="toggleStatus({{ $banner->id }})"
                                                @if($banner->status == '1') checked @endif>
                                        </div>
                                    </td>

                                    {{-- Created by --}}
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $banner->creator->name ?? '—' }}
                                        </p>
                                    </td>

                                    {{-- Created at --}}
                                    <td>
                                        <p class="text-xs font-weight-bold mb-0">
                                            {{ $banner->created_at->format('d M Y') }}
                                        </p>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="align-middle text-center">
                                        <button
                                            class="btn btn-outline-primary select-md btn_action btn_outline"
                                            data-bs-toggle="modal"
                                            data-bs-target="#bannerModal"
                                            wire:click="openEdit({{ $banner->id }})"
                                            title="Edit Banner">
                                            Edit
                                        </button>
                                        <button
                                            class="btn btn-outline-danger select-md btn_outline"
                                            wire:click="confirmDelete({{ $banner->id }})"
                                            title="Delete Banner">
                                            Delete
                                        </button>
                                    </td>

                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center text-secondary py-4">
                                        No banners found. Click "Add Banner" to create one.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                    <div class="mt-3">
                        <nav aria-label="Page navigation">
                            {{-- {{ $banners->links() }} --}}
                        </nav>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- <div class="loader-container" wire:loading>
        <div class="loader"></div>
    </div> --}}

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>

<script>
    window.addEventListener('close-banner-modal', () => {
        var el = document.getElementById('bannerModal');
        var modal = bootstrap.Modal.getInstance(el);
        if (modal) modal.hide();
    });

    window.addEventListener('open-banner-modal', () => {
        var el = document.getElementById('bannerModal');
        var modal = new bootstrap.Modal(el);
        modal.show();
    });

    window.addEventListener('showDeleteConfirm', function (event) {
        let itemId = event.detail[0].itemId;
        Swal.fire({
            title: "Are you sure?",
            text: "This action cannot be undone!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                @this.call('deleteBanner', itemId);
                Swal.fire("Deleted!", "The banner has been deleted.", "success");
            }
        });
    });


    document.addEventListener('livewire:init', () => {

        let tbody = document.querySelector("#bannerTableBody");

        Sortable.create(tbody, {
            animation: 150,

            onEnd: function () {

                let order = [];

                document.querySelectorAll("#bannerTableBody tr")
                    .forEach((row, index) => {

                        order.push({
                            id: row.dataset.id,
                            position: index + 1
                        });

                    });

                Livewire.dispatch('updateBannerOrder', {
                    order: order
                });

            }
        });

    });
</script>
@endpush
</div>