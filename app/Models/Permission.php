<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Permission extends Model
{
    protected $fillable = ['slug', 'label', 'group'];

    public static array $all = [
        'guests' => [
            'guests.view'    => 'Xem danh sách khách',
            'guests.create'  => 'Thêm khách mới',
            'guests.edit'    => 'Sửa thông tin khách',
            'guests.delete'  => 'Xóa khách',
            'guests.export'  => 'Tải xuống QR code',
            'guests.lock'    => 'Khóa / Mở khóa QR',
        ],
        'stations' => [
            'stations.view'   => 'Xem stations',
            'stations.manage' => 'Quản lý stations (thêm/sửa/xóa)',
        ],
        'stats' => [
            'stats.view' => 'Xem thống kê',
        ],
        'users' => [
            'users.manage' => 'Quản lý người dùng',
            'roles.manage' => 'Quản lý vai trò & phân quyền',
        ],
    ];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'role_permission');
    }
}
