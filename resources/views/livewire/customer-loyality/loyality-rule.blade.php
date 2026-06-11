<div class="container">

    <section class="admin__title mb-5">
        <h5>Loyality Rule</h5>
    </section>

    <div class="text-end mb-3">
        <a href="#" class="btn btn-outline-success select-md"
           data-bs-toggle="modal" data-bs-target="#addRuleModal">
            Add Rule
        </a>
    </div>

    <!-- TABLE -->
   <div class="card my-4">
    <div class="card-body pb-0">

        @if (session()->has('success'))
            <div class="alert alert-success m-3">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive p-0">
            <table class="table align-items-center mb-0">

                <thead class="">
                    <tr>
                        <th>Range</th>
                        <th>Reward Type</th>
                        <th>Benefit</th>
                        <th>Status</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>

                <tbody>

                    @forelse($rules as $rule)
                        <tr class="">

                            <!-- RANGE -->
                            <td>
                                <span class="fw-semibold text-dark">
                                    ₹{{ number_format($rule->min_amount) }}
                                    -
                                    ₹{{ number_format($rule->max_amount) }}
                                </span>
                            </td>

                            <!-- REWARD TYPE -->
                            <td>
                                <span class="badge 
                                    {{ $rule->reward_type == 'points' ? 'bg-primary' : 'bg-warning text-dark' }}">
                                    {{ ucfirst($rule->reward_type) }}
                                </span>
                            </td>

                            <!-- BENEFIT -->
                            <td>
                                @if($rule->reward_type == 'points')
                                    <span class="fw-semibold text-success">
                                        {{ $rule->points_value }}
                                        {{ $rule->points_type == 'percentage' ? '%' : 'Pts' }}
                                    </span>
                                @elseif($rule->reward_type == 'lounge')
                                    <span class="fw-semibold text-info">
                                        {{ $rule->lounge_visits }} Lounge Visits
                                    </span>
                                @endif
                            </td>

                            <!-- STATUS TOGGLE -->
                            <td>
                                <div class="form-check form-switch">
                                    <input class="form-check-input"
                                           type="checkbox"
                                           wire:click="toggleStatus({{ $rule->id }})"
                                           {{ $rule->status ? 'checked' : '' }}>
                                </div>
                            </td>

                            <!-- ACTIONS -->
                            <td class="text-center">

                                    <button class="btn btn-outline-primary select-md btn_action btn_outline"
                                            wire:click="editRule({{ $rule->id }})"
                                            data-bs-toggle="modal"
                                            data-bs-target="#addRuleModal">
                                        Edit
                                    </button>

                                    <!--<button class="btn btn-outline-danger select-md btn_outline"-->
                                    <!--        wire:click="confirmDelete({{ $rule->id }})">-->
                                    <!--    Delete-->
                                    <!--</button>-->

                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="text-muted">
                                    No loyalty rules found
                                </div>
                            </td>
                        </tr>
                    @endforelse

                </tbody>

            </table>
        </div>

    </div>
</div>

    <!-- MODAL -->
    <div wire:ignore.self class="modal fade" id="addRuleModal">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5>{{ $isEdit ? 'Edit' : 'Add' }} Rule</h5>
                    <button class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <!-- RANGE -->
                    <div class="row">
                        <div class="col-md-6">
                            <input type="number" class="form-control mb-2"
                                   wire:model="min_amount" placeholder="Min Amount">
                            @error('min_amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>

                        <div class="col-md-6">
                            <input type="number" class="form-control mb-2"
                                   wire:model="max_amount" placeholder="Max Amount">
                            @error('max_amount') <span class="text-danger">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <!-- REWARD TYPE -->
                    <label>Reward Type</label>
                    <div class="d-flex gap-2 mb-3">

                        <button type="button"
                            class="flex-1 py-2 text-xs font-bold rounded border
                            {{ $reward_type=='points' ? 'bg-primary text-white' : '' }}"
                            wire:click="$set('reward_type','points')">
                            Points
                        </button>

                        <button type="button"
                            class="flex-1 py-2 text-xs font-bold rounded border
                            {{ $reward_type=='lounge' ? 'bg-primary text-white' : '' }}"
                            wire:click="$set('reward_type','lounge')">
                            Lounge
                        </button>

                       

                    </div>

                 <!-- POINTS -->
                @if($reward_type == 'points')
                    <select class="form-control mb-2" wire:model="points_type">
                        <option value="">Select Type</option>
                        <option value="percentage">Percentage</option>
                    </select>
                    @error('points_type') <span class="text-danger">{{ $message }}</span> @enderror
                
                    <input type="number" class="form-control mb-2"
                           wire:model="points_value" placeholder="Points Value">
                           
                    @error('points_value') <span class="text-danger">{{ $message }}</span> @enderror
                    
                     <!-- EXPIRY -->
                    <input type="number"
                           class="form-control mb-2"
                           wire:model="points_expiry_days"
                           placeholder="Points Expiry Days">
                           
                    @error('points_expiry_days')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                    
                    

                @endif
                
                <!-- LOUNGE -->
                @if($reward_type == 'lounge')
                    <input type="number" class="form-control mb-2"
                           wire:model="lounge_visits" placeholder="Lounge Visits">
                    @error('lounge_visits') <span class="text-danger">{{ $message }}</span> @enderror
                    
                    <!-- EXPIRY -->
                    <input type="number"
                           class="form-control mb-2"
                           wire:model="lounge_expiry_days"
                           placeholder="Lounge Expiry Days">
                
                    @error('lounge_expiry_days')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                @endif
                
                    <input type="date"
                               class="form-control mb-2"
                               wire:model="effective_date">
                        
                    @error('effective_date')
                        <span class="text-danger">{{ $message }}</span>
                    @enderror
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>

                    <button class="btn btn-success"
                            wire:click="saveRule">
                        Save
                    </button>
                </div>

            </div>
        </div>
    </div>

</div>

@push('js')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
window.addEventListener('closeModal', () => {
    let modal = bootstrap.Modal.getInstance(document.getElementById('addRuleModal'));
    modal.hide();
});

window.addEventListener('swal:confirmDelete', event => {
    Swal.fire({
        title: 'Delete?',
        icon: 'warning',
        showCancelButton: true
    }).then((res) => {
        if (res.isConfirmed) {
            @this.call('deleteRule', event.detail.id);
        }
    });
});
</script>
@endpush