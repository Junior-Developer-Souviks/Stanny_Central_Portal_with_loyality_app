<?php

namespace App\Http\Livewire\CustomerLoyality;

use App\Models\WalletTransaction;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\StreamedResponse;


class LoyaltyLedger extends Component
{
    use WithPagination;
     protected $paginationTheme = 'bootstrap';
    /*
    |--------------------------------------------------------------------------
    | Filter state — bound to blade inputs via wire:model
    |--------------------------------------------------------------------------
    */
    public string  $search  = '';
    public string  $from    = '';
    public string  $to      = '';
    public string  $type    = '';      // earned | redeemed | expired | adjusted
    public string  $channel = '';
    public string  $tab     = 'all';  // all | points | lounge
 
    /*
    |--------------------------------------------------------------------------
    | Sort state — matches your order index pattern
    |--------------------------------------------------------------------------
    */
    public string $sortField     = 'id';
    public string $sortDirection = 'desc';
 
    /*
    |--------------------------------------------------------------------------
    | Pagination page names — separate paginators for each table
    |--------------------------------------------------------------------------
    */
    protected string $pointsPageName  = 'points_page';
    protected string $loungePageName  = 'lounge_page';
 
    /*
    |--------------------------------------------------------------------------
    | Livewire hooks
    |--------------------------------------------------------------------------
    */
    public function updatedSearch(): void  { $this->resetPage($this->pointsPageName); $this->resetPage($this->loungePageName); }
    public function updatedFrom(): void    { $this->resetPage($this->pointsPageName); $this->resetPage($this->loungePageName); }
    public function updatedTo(): void      { $this->resetPage($this->pointsPageName); $this->resetPage($this->loungePageName); }
    public function updatedType(): void    { $this->resetPage($this->pointsPageName); $this->resetPage($this->loungePageName); }
    public function updatedChannel(): void { $this->resetPage($this->pointsPageName); $this->resetPage($this->loungePageName); }
    public function updatedTab(): void     { $this->resetPage($this->pointsPageName); $this->resetPage($this->loungePageName); }
 
    public function applyFilters(): void
    {
        $this->resetPage($this->pointsPageName);
        $this->resetPage($this->loungePageName);
    }
 
    public function clearFilters(): void
    {
        $this->reset(['search', 'from', 'to', 'type', 'channel', 'tab']);
        $this->resetPage($this->pointsPageName);
        $this->resetPage($this->loungePageName);
    }
 
    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField     = $field;
            $this->sortDirection = 'asc';
        }
    }
 
 
    /*
    |--------------------------------------------------------------------------
    | Shared base query — filters applied once, reused for both tables
    |--------------------------------------------------------------------------
    */
    private function baseQuery()
    {
        $query = WalletTransaction::with(['customer:id,name,phone', 'redeemedBy:id,name']);
 
        // Search: customer name or phone
        if ($this->search) {
            $search = $this->search;
            $query->whereHas('customer', function ($q) use ($search) {
                $q->where('name',  'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
 
        // Date range
        if ($this->from) { $query->whereDate('created_at', '>=', $this->from); }
        if ($this->to)   { $query->whereDate('created_at', '<=', $this->to);   }
 
        // Channel
        if ($this->channel) { $query->where('channel', $this->channel); }
 
        // Type filter
        if ($this->type) {
            switch ($this->type) {
                case 'earned':
                    $query->where('type', 'credit')->where('is_expired', 0)
                          ->whereNotIn('source', ['bonus', 'manual_adjustment', 'adjustment']);
                    break;
                case 'redeemed':
                    $query->where('type', 'debit')->where('is_expired', 0);
                    break;
                case 'expired':
                    $query->where(fn($q) => $q->where('is_expired', 1)->orWhere('expiry_date', '<', now()));
                    break;
                case 'adjusted':
                    $query->whereIn('source', ['bonus', 'manual_adjustment', 'adjustment']);
                    break;
            }
        }
 
        return $query;
    }
 
 
    /*
    |--------------------------------------------------------------------------
    | Points query
    |--------------------------------------------------------------------------
    */
    private function pointsQuery()
    {
        return $this->baseQuery()
            ->where('points', '>', 0)
            ->orderBy($this->sortField === 'customer_name' ? 'id' : $this->sortField, $this->sortDirection);
    }
 
    /*
    |--------------------------------------------------------------------------
    | Lounge query
    |--------------------------------------------------------------------------
    */
    private function loungeQuery()
    {
        $sortField = match($this->sortField) {
            'points' => 'lounge_visits',
            'customer_name' => 'id',
            default => $this->sortField,
        };
 
        return $this->baseQuery()
            ->where('lounge_visits', '>', 0)
            ->orderBy($sortField, $this->sortDirection);
    }
 
 
    /*
    |--------------------------------------------------------------------------
    | Summary metrics — 8 cards (4 points + 4 lounge)
    |--------------------------------------------------------------------------
    */
    private function getSummary(): array
    {
        $base = $this->baseQuery();
 
        // Points
        $pIssued   = (clone $base)->where('type', 'credit')->where('points', '>', 0)->sum('points');
        $pRedeemed = (clone $base)->where('type', 'debit')->where('points', '>', 0)->where('is_expired', 0)->sum('points');
        $pExpired  = (clone $base)->where('points', '>', 0)
                        ->where(fn($q) => $q->where('is_expired', 1)->orWhere('expiry_date', '<', now()))
                        ->sum('points');
 
        // Lounge
        $lIssued   = (clone $base)->where('type', 'credit')->where('lounge_visits', '>', 0)->sum('lounge_visits');
        $lRedeemed = (clone $base)->where('type', 'debit')->where('lounge_visits', '>', 0)->where('is_expired', 0)->sum('lounge_visits');
        $lExpired  = (clone $base)->where('lounge_visits', '>', 0)
                        ->where(fn($q) => $q->where('is_expired', 1)->orWhere('expiry_date', '<', now()))
                        ->sum('lounge_visits');
 
        return [
            'points' => [
                'issued'          => (float) $pIssued,
                'redeemed'        => (float) $pRedeemed,
                'expired'         => (float) $pExpired,
                'net_outstanding' => (float) ($pIssued - $pRedeemed - $pExpired),
            ],
            'lounge' => [
                'issued'          => (int) $lIssued,
                'redeemed'        => (int) $lRedeemed,
                'expired'         => (int) $lExpired,
                'net_outstanding' => (int) ($lIssued - $lRedeemed - $lExpired),
            ],
        ];
    }
 
 
    /*
    |--------------------------------------------------------------------------
    | CSV Export — streams both points and lounge sections
    |--------------------------------------------------------------------------
    */
    public function export(): StreamedResponse
    {
        $filename = 'ledger_' . now()->format('Y-m-d_His') . '.csv';
 
        $pointsQ = $this->pointsQuery();
        $loungeQ = $this->loungeQuery();
 
        return response()->streamDownload(function () use ($pointsQ, $loungeQ) {
            $handle = fopen('php://output', 'w');
 
            // ── Points section ────────────────────────────────────────────────
            fputcsv($handle, ['=== POINTS LEDGER ===']);
            fputcsv($handle, ['Date & Time','Customer','Phone','Type','Points','Balance Before','Balance After','Channel','Source','Redeemed By','Expiry Date','Expired']);
 
            $pointsQ->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $t) {
                    $typeLabel = $t->is_expired ? 'expired'
                        : ($t->type === 'credit'
                            ? (in_array($t->source, ['bonus','manual_adjustment','adjustment']) ? 'adjusted' : 'earned')
                            : 'redeemed');
                    fputcsv($handle, [
                        \Carbon\Carbon::parse($t->created_at)->format('d M Y H:i'),
                        $t->customer->name  ?? '',
                        $t->customer->phone ?? '',
                        $typeLabel,
                        $t->type === 'debit' ? -abs($t->points) : $t->points,
                        $t->balance_before ?? '',
                        $t->balance_after  ?? '',
                        $t->channel  ?? '',
                        $t->source   ?? '',
                        $t->redeemedBy->name ?? '',
                        $t->expiry_date ?? '',
                        $t->is_expired ? 'Yes' : 'No',
                    ]);
                }
            });
 
            fputcsv($handle, []);
            fputcsv($handle, []);
 
            // ── Lounge section ─────────────────────────────────────────────────
            fputcsv($handle, ['=== LOUNGE VISITS LEDGER ===']);
            fputcsv($handle, ['Date & Time','Customer','Phone','Type','Lounge Visits','Before','After','Used','Channel','Source','Redeemed By','Expiry Date','Expired']);
 
            $loungeQ->chunk(500, function ($rows) use ($handle) {
                foreach ($rows as $t) {
                    $typeLabel = $t->is_expired ? 'expired' : ($t->type === 'credit' ? 'earned' : 'redeemed');
                    fputcsv($handle, [
                        \Carbon\Carbon::parse($t->created_at)->format('d M Y H:i'),
                        $t->customer->name  ?? '',
                        $t->customer->phone ?? '',
                        $typeLabel,
                        $t->type === 'debit' ? -abs($t->lounge_visits) : $t->lounge_visits,
                        $t->lounge_before ?? '',
                        $t->lounge_after  ?? '',
                        $t->lounge_used   ?? 0,
                        $t->channel  ?? '',
                        $t->source   ?? '',
                        $t->redeemedBy->name ?? '',
                        $t->expiry_date ?? '',
                        $t->is_expired ? 'Yes' : 'No',
                    ]);
                }
            });
 
            fclose($handle);
 
        }, $filename, ['Content-Type' => 'text/csv']);
    }
 
 
    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */
        public function render()
        {
            $pointsTransactions = ($this->tab === 'lounge')
                ? WalletTransaction::whereNull('id')->paginate(50, ['*'], $this->pointsPageName)
                : $this->pointsQuery()->paginate(50, ['*'], $this->pointsPageName);
    
            $loungeTransactions = ($this->tab === 'points')
                ? WalletTransaction::whereNull('id')->paginate(50, ['*'], $this->loungePageName)
                : $this->loungeQuery()->paginate(50, ['*'], $this->loungePageName);
    
            return view('livewire.customer-loyality.loyalty-ledger', [
                'pointsTransactions' => $pointsTransactions,
                'loungeTransactions' => $loungeTransactions,
                'totalPoints'        => $this->pointsQuery()->count(),
                'totalLounge'        => $this->loungeQuery()->count(),
                'summary'            => $this->getSummary(),
            ]);
        }
    }
    
