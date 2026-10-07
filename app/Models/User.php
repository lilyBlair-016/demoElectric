<?php
namespace App\Models;
use CodeIgniter\Model;
class User extends Model
{
protected $DBGroup = 'auth';
protected $table = 'users';
protected $primaryKey = 'id';
protected $useAutoIncrement = true;
protected $returnType = 'array';
protected $useSoftDeletes = false;
protected $protectFields = true;
protected $allowedFields = [
'first_name', 'last_name', 'email', 'phone', 'address',
'city', 'state', 'zip_code', 'password', 'user_type',
'is_active', 'email_verified'
];
protected bool $allowEmptyInserts = false;
protected bool $updateOnlyChanged = true;
protected array $casts = [
'is_active' => '?boolean',
'email_verified' => '?boolean',
];
protected array $castHandlers = [];
// Dates
protected $useTimestamps = true;
protected $dateFormat = 'datetime';
protected $createdField = 'created_at';
protected $updatedField = 'updated_at';
protected $deletedField = 'deleted_at';
// Validation
protected $validationRules = [
'id' => 'permit_empty|is_natural_no_zero',
'first_name' => 'required|min_length[2]|max_length[100]',
'last_name' => 'required|min_length[2]|max_length[100]',
'email' => 'required|valid_email|is_unique[users.email,id,{id}]',
'phone' => 'permit_empty|min_length[10]|max_length[20]',
'password' => 'required|min_length[8]',
'user_type' => 'permit_empty|in_list[customer,admin]'
];
protected $validationMessages = [
'email' => [
'is_unique' => 'This email address is already registered.'
],
'password' => [
'min_length' => 'Password must be at least 8 characters long.'
]
];
protected $skipValidation = false;
protected $cleanValidationRules = true;
// Callbacks
protected $allowCallbacks = true;
protected $beforeInsert = ['hashPassword'];
protected $afterInsert = [];
protected $beforeUpdate = ['hashPassword'];
protected $afterUpdate = [];
protected $beforeFind = [];
protected $afterFind = [];
protected $beforeDelete = [];
protected $afterDelete = [];
protected function hashPassword(array $data)
{
if (isset($data['data']['password'])) {
$data['data']['password'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
}
return $data;
}
public function verifyPassword($password, $hash)
{
// New registrations are hashed. Keep the professor's plaintext compatibility
// only for non-hash values; a stored hash must never work as the password.
if (password_get_info($hash)['algoName'] !== 'unknown') {
    return password_verify($password, $hash);
}
return $hash !== '' && hash_equals($hash, $password);
}
public function findByEmail($email)
{
return $this->where('email', $email)->first();
}
}
