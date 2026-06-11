<div class="container-fluid px-2 px-md-4">

    <section class="admin__title">
        <h5>Settings</h5>
    </section>

    <section>
        <ul class="breadcrumb_menu">
            <li><a href="#">Settings</a></li>
            <li>Manage Settings</li>

            <li class="back-button">
               
            </li>
        </ul>
    </section>

    <div class="card card-body">

        <div class="card card-plain h-100">
             @if (session()->has('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if (session()->has('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
            <div class="card-header pb-0 p-3">
                <div class="row justify-content-between">

                    <div class="col-12">
                        <h6 class="badge bg-danger custom_danger_badge">
                            Loyalty Settings
                        </h6>
                    </div>

                </div>
            </div>

            <div class="card-body p-3">

                <form wire:submit.prevent="updateSettings">

                    <div class="row">

                        <!-- Welcome Bonus -->
                        <div class="mb-3 col-md-6">
                            <label class="form-label">
                                Welcome Bonus <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   wire:model="welcome_bonus"
                                   class="form-control form-control-sm border border-1 p-2"
                                   placeholder="Enter Welcome Bonus">

                            @error('welcome_bonus')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Expiry Days -->
                        <div class="mb-3 col-md-6">
                            <label class="form-label">
                                Point Expiry Days <span class="text-danger">*</span>
                            </label>

                            <input type="number"
                                   wire:model="point_expiry_days"
                                   class="form-control form-control-sm border border-1 p-2"
                                   placeholder="Enter Expiry Days">

                            @error('point_expiry_days')
                                <div class="text-danger">{{ $message }}</div>
                            @enderror
                        </div>

                    </div>
                    
                    {{-- ================= REDEMPTION RULES ================= --}}
                    <div class="row mt-3">

                        <div class="col-12">
                            <h6 class="badge bg-primary custom_danger_badge">
                                Redemption Ratios
                            </h6>
                        </div>

                        @foreach($rules as $index => $rule)

                            <div class="col-md-6 mb-3">

                                <label class="form-label text-uppercase">
                                    {{ $rule['channel'] }}
                                </label>

                                <input type="hidden"
                                       wire:model="rules.{{ $index }}.id">

                                <input type="number"
                                       step="0.01"
                                       wire:model="rules.{{ $index }}.ratio"
                                       class="form-control form-control-sm border border-1 p-2">

                                @error('rules.' . $index . '.ratio')
                                    <span class="text-danger">{{ $message }}</span>
                                @enderror

                            </div>

                        @endforeach

                    </div>

                    <button type="submit"
                            class="btn btn-outline-success select-md">
                        <i class="material-icons me-1">save</i>
                        Update Settings
                    </button>

                </form>

            </div>
        </div>
    </div>
</div>