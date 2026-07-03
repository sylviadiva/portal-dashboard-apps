<?php

namespace App\Services;

use App\Models\Application;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AccessService
{
    /**
     * Effective access untuk 1 user. Cocok dipakai untuk dashboard
     * user yang sedang login (cuma butuh data 1 orang).
     */
    public function getEffectiveApplications(User $user): Collection
    {
        $fromDepartment = Application::query()
            ->select('applications.*')
            ->join('app_department', 'app_department.application_id', '=', 'applications.id')
            ->where('app_department.department_id', $user->department_id);

        $fromRole = Application::query()
            ->select('applications.*')
            ->join('app_role', 'app_role.application_id', '=', 'applications.id')
            ->where('app_role.role_id', $user->role_id);

        $fromUser = Application::query()
            ->select('applications.*')
            ->join('app_user', 'app_user.application_id', '=', 'applications.id')
            ->where('app_user.user_id', $user->id);

        return $fromDepartment->unionAll($fromRole)->unionAll($fromUser)
            ->get()
            ->unique('id')
            ->values();
    }

    /**
     * Effective access untuk SEMUA user sekaligus, dalam 1 query database saja
     * (bukan 3N query seperti kalau manggil getEffectiveApplications() di loop).
     *
     * Caranya: bikin 1 UNION ALL yang isinya baris (user_id, application_id, ...data
     * aplikasi) dari ke-3 sumber sekaligus, JOIN langsung ke tabel users supaya
     * semua user yang relevan ikut kebawa dalam 1 query. Hasilnya di-group per
     * user_id + dedup di PHP.
     */
    public function getEffectiveAccessMatrix(): Collection
    {
        $fromDepartment = DB::table('users as u')
            ->join('app_department as ad', 'ad.department_id', '=', 'u.department_id')
            ->join('applications as a', 'a.id', '=', 'ad.application_id')
            ->select('u.id as user_id', 'a.*');

        $fromRole = DB::table('users as u')
            ->join('app_role as ar', 'ar.role_id', '=', 'u.role_id')
            ->join('applications as a', 'a.id', '=', 'ar.application_id')
            ->select('u.id as user_id', 'a.*');

        $fromUser = DB::table('app_user as au')
            ->join('applications as a', 'a.id', '=', 'au.application_id')
            ->select('au.user_id as user_id', 'a.*');

        $rows = $fromDepartment->unionAll($fromRole)->unionAll($fromUser)->get();

        return $rows
            ->groupBy('user_id')
            ->map(fn (Collection $userRows) => $userRows->unique('id')->values());
    }
}