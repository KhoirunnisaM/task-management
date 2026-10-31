<?php

return [
    'required' => ':attribute wajib diisi.',
    'string' => ':attribute harus berupa teks.',
    'integer' => ':attribute harus berupa angka.',
    'email' => ':attribute harus berupa alamat email yang valid.',
    'max' => [
        'string' => ':attribute maksimal :max karakter.',
    ],
    'date' => ':attribute harus berupa tanggal yang valid.',
    'after_or_equal' => ':attribute harus sama dengan atau setelah :date.',
    'unique' => ':attribute sudah digunakan.',
    'exists' => ':attribute yang dipilih tidak valid.',
    'in' => ':attribute yang dipilih tidak valid.',

    'attributes' => [
        'title' => 'judul',
        'description' => 'deskripsi',
        'status' => 'status',
        'start_date' => 'tanggal mulai',
        'end_date' => 'tanggal selesai',
        'email' => 'email',
        'password' => 'password',
        'user_id' => 'pengguna',
        'role' => 'peran',
    ],
];
