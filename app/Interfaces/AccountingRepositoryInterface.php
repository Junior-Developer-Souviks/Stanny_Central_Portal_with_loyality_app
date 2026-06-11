<?php
// app/Interfaces/UserRepositoryInterface.php
namespace App\Interfaces;

interface AccountingRepositoryInterface
{
    public function StorePaymentReceipt(array $data);
    public function StoreOpeningBalance(array $data);
    // public function find($id);
    // public function create(array $data);
    // public function update($id, array $data);
    // public function delete($id);
}
