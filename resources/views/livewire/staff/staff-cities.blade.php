<div class="flex items-center justify-center min-h-screen bg-gray-100">
    {{-- Create city --}}
    @if ($cityCreated)
    <div class="w-1/3 bg-white p-6 ml-4 rounded-lg shadow-md">
        <h5 class="text-lg font-semibold text-gray-700 mb-4">Create City</h5>
        <form wire:submit.prevent="store" class="space-y-4">
            <!-- Fabric Title -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-600"> City</label>
                <input type="text" id="city" wire:model="city"
                    class="form-control border border-2 p-2 w-full @error('city') border-red-500 @enderror"
                    placeholder="Enter city">
                @error('city')
                    <span class="text-sm text-danger">{{ $message }}</span>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="btn btn-primary btn-sm mt-4">
                   Store
                </button>
                <a href="#" wire:click.prevent="showCitySelection" class="btn btn-dark btn-sm mt-4">
                   Show
                </a>
            </div>
        </form>
    </div>
    @else
    {{-- Select city --}}
    <div class="w-2/3 bg-white p-6 rounded-lg shadow-md">
        <form wire:submit.prevent="submit" class="space-y-4">
            <!-- Title -->
            <h3 class="text-xl font-semibold text-gray-700"> Cities Assigned to {{ $salesmanName }}</h3>

            <!-- City Select Box -->
            <div>
                <label for="city" class="block text-sm font-medium text-gray-600">City</label>
                <select id="city" wire:model="selectedCity"
                    class="form-select border border-2 p-2 w-full @error('selectedCity') border-red-500 @enderror">
                    <option value="">-- Choose a City --</option>
                    @foreach ($cities as $city)
                        <option value="{{ $city->id }}">{{ $city->name }}</option>
                    @endforeach
                </select>
            </div>
            @error('selectedCity')
                <div class="text-danger">{{ $message }}</div>
            @enderror
           

            @if (!empty($selectedCities))
            <div class="mt-4 p-2 bg-blue-100 text-blue-700 rounded-md text-sm">
                <p>You selected:</p>
                <ul>
                    @foreach ($selectedCities as $item)
                        <li class="flex justify-between items-center">
                            {{ $item->city->name }}
                            <span wire:click="deleteCity({{ $item->id }})" class="cursor-pointer text-red-600 hover:text-red-800 ml-2">
                                ❌
                            </span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
            <!-- Buttons -->
            <div class="flex justify-between items-center mt-4">
                <button type="button" onclick="window.history.back();"
                    class="btn btn-dark btn-sm">
                    Back
                </button>
                <button type="submit"
                    class="btn btn-primary btn-sm">
                    Submit
                </button>
                <button type="button" wire:click.prevent="AddNewCity"
                    class="btn btn-success btn-sm">
                    Add New
                </button>
            </div>
        </form>

        <!-- Display Messages -->
        @if (session()->has('message'))
            <div class="mt-4 p-2 bg-green-100 text-green-700 rounded-md text-sm">
                {{ session('message') }}
            </div>
        @endif

        @if (session()->has('error'))
            <div class="alert alert-danger" role="alert">
                {{ session('error') }}
            </div>
        @endif
    </div>
    @endif
</div>
