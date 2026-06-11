<?php

namespace App\Http\Livewire\Staff;

use Livewire\Component;
use App\Models\City;
use App\Models\UserCity;
use App\Models\User;

class StaffCities extends Component
{
    public $city;
    public $cities;  
    public $selectedCity;  
    public $selectedCities;  
    public $submittedCities = [];
    public $cityCreated = false;
    public $salesman_id;
    public $salesmanName;

    protected $rules = [
        'selectedCity' => 'required|exists:cities,id',  
    ];

    public function mount($salesman_id)
    {
        $this->salesman_id = $salesman_id;
        $this->salesmanName = User::find($salesman_id)->name;
       // Exclude cities already assigned to other salesmen
       $this->RefreshCities();
    }

    public function RefreshCities(){
        $assignedCityIds = UserCity::where('user_id', $this->salesman_id)->pluck('city_id')->toArray();
        $this->selectedCities = UserCity::with('city')->where('user_id', $this->salesman_id)->get();
        $this->cities = City::whereNotIn('id', $assignedCityIds)->get();
    }

    public function checkIfCityAssignedToAnotherSalesman($cityId)
    {
        $existingAssignment = UserCity::where('city_id', $cityId)
                                      ->where('user_id', '!=', $this->salesman_id)
                                      ->exists(); // Check if the city is assigned to another user
        if ($existingAssignment) {
            session()->flash('error', 'This city is already assigned to another salesman.');
        }else{
            UserCity::create(['user_id' => $this->salesman_id, 'city_id' => $cityId]);
            $this->RefreshCities();
        }
    }

    public function showCitySelection(){
        $this->cityCreated = false;
    }

    public function AddNewCity(){
        $this->cityCreated = true;
    }

    public function store(){
        $this->validate([
            'city' => 'required|unique:cities,name'
        ]);

         // Logic to store the city
        $newCity = City::create(['name' => $this->city]);

        // Check if the city is already assigned to another salesman
        $this->checkIfCityAssignedToAnotherSalesman($newCity->id);

         $this->cities = City::all(); // Refresh the cities list
        $this->submittedCities[] = $newCity->name; // Add to the submittedCities list
        session()->put('submittedCities', $this->submittedCities);

        $this->city = null;
        $this->showCitySelection();
        $this->RefreshCities();
        session()->flash('message', 'City created successfully!');
    }
    
    
    public function submit(){

         $this->validate();

         if ($this->selectedCity) {
             // Check if the selected city is already assigned to another salesman
             $this->checkIfCityAssignedToAnotherSalesman($this->selectedCity);
            $cityName = City::find($this->selectedCity)->name;

            if (!in_array($cityName, $this->submittedCities)) {
                $this->submittedCities[] = $cityName;
            }

            session()->put('submittedCities', $this->submittedCities);
        }

        $this->selectedCity = null;
    }

    public function deleteCity($id)
    {
        UserCity::where('id', $id)->delete();
        $this->RefreshCities();
    }

    public function render()
    {
        return view('livewire.staff.staff-cities');
    }
}
