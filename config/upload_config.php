<?php

/**
 * Upload Configuration Constants
 */
class UploadConfig
{
    // File size limits (in bytes)
    const MAX_FILE_SIZE = 5242880; // 5MB
    const MAX_TOTAL_UPLOAD_SIZE = 20971520; // 20MB total

    // Allowed file types
    const ALLOWED_IMAGE_TYPES = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    const ALLOWED_DOC_TYPES = ['pdf', 'doc', 'docx', 'txt', 'zip', 'rar', '7z'];

    // Allowed MIME types for validation
    const ALLOWED_MIME_TYPES = [
        'jpg' => ['image/jpeg', 'image/pjpeg'],
        'jpeg' => ['image/jpeg', 'image/pjpeg'],
        'png' => ['image/png'],
        'gif' => ['image/gif'],
        'webp' => ['image/webp'],
        'pdf' => ['application/pdf'],
        'doc' => ['application/msword'],
        'docx' => ['application/vnd.openxmlformats-officedocument.wordprocessingml.document'],
        'txt' => ['text/plain'],
        'zip' => ['application/zip', 'application/x-zip-compressed'],
        'rar' => ['application/x-rar-compressed'],
        '7z' => ['application/x-7z-compressed']
    ];

    // Upload directories (relative to document root)
    const UPLOAD_DIRS = [
        'pictures' => './uploads/pictures/',
        'files' => './uploads/files/',
        'attachments' => './uploads/attachments/'
    ];

    // Validation rules for form fields
    const VALIDATION_RULES = [
        'name' => [
            'required' => true,
            'min_length' => 2,
            'max_length' => 100,
            'pattern' => '/^[a-zA-Z\s\-\'\.]+$/u'
        ],
        'email' => [
            'required' => true,
            'max_length' => 255,
            'pattern' => '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/'
        ],
        'phone' => [
            'required' => false,
            'max_length' => 20,
            'pattern' => '/^[\d\s\-\+\(\)]{10,20}$/'
        ],
        'note' => [
            'required' => false,
            'max_length' => 2000
        ],
        'address' => [
            'required' => false,
            'max_length' => 500
        ],
        'url' => [
            'required' => false,
            'max_length' => 500,
            'pattern' => '/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/'
        ],
        'location' => [
            'required' => false,
            'max_length' => 100
        ]
    ];

    // Rate limiting
    const RATE_LIMIT = [
        'max_requests' => 10,
        'time_window' => 60 // seconds
    ];
}
