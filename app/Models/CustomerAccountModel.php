<?php

namespace App\Models;

use CodeIgniter\Model;

class CustomerAccountModel extends Model
{
    public const CONNECTION_TYPES = ['residential', 'commercial', 'industrial'];
    public const STATUSES = ['active', 'inactive', 'suspended'];

    protected $table = 'customer_accounts';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $allowedFields = [
        'account_number', 'customer_name', 'address', 'email',
        'meter_number', 'phone', 'connection_type', 'status',
    ];

    // Match the professor's model and the two TIMESTAMP columns.
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getSummaryCounts(): array
    {
        // Count all records, independently of the ten-row paginated list.
        // countAllResults() resets the query after each count.
        return [
            'total' => $this->countAllResults(),
            'residential' => $this->where('connection_type', 'residential')->countAllResults(),
            'commercial' => $this->where('connection_type', 'commercial')->countAllResults(),
            'industrial' => $this->where('connection_type', 'industrial')->countAllResults(),
            'active' => $this->where('status', 'active')->countAllResults(),
        ];
    }

    public function getAccountsPaginated(int $perPage = 10): array
    {
        // ID breaks ties when imported records have identical timestamps.
        return $this->orderBy('created_at', 'DESC')->orderBy('id', 'DESC')->paginate($perPage);
    }
}
