<?php

return [
    'name' => 'plugins/newsletter::newsletter.settings.email.templates.title',
    'description' => 'plugins/newsletter::newsletter.settings.email.templates.description',
    'templates' => [
        'subscriber_email' => [
            'title' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.title',
            'description' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.description',
            'subject' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.subject',
            'can_off' => true,
            'variables' => [
                'newsletter_name' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.newsletter_name',
                'newsletter_email' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.newsletter_email',
                'newsletter_unsubscribe_link' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.newsletter_unsubscribe_link',
                'newsletter_unsubscribe_url' => 'plugins/newsletter::newsletter.settings.email.templates.to_user.newsletter_unsubscribe_url',
            ],
        ],
        'admin_email' => [
            'title' => 'plugins/newsletter::newsletter.settings.email.templates.to_admin.title',
            'description' => 'plugins/newsletter::newsletter.settings.email.templates.to_admin.description',
            'subject' => 'plugins/newsletter::newsletter.settings.email.templates.to_admin.subject',
            'can_off' => true,
            'variables' => [
                'newsletter_email' => 'plugins/newsletter::newsletter.settings.email.templates.to_admin.newsletter_email',
            ],
        ],
        'new_post_notification' => [
            'title' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.title',
            'description' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.description',
            'subject' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.subject',
            'can_off' => true,
            'variables' => [
                'newsletter_name' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.newsletter_name',
                'newsletter_email' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.newsletter_email',
                'post_title' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.post_title',
                'post_excerpt' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.post_excerpt',
                'post_content' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.post_content',
                'post_url' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.post_url',
                'post_image' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.post_image',
                'newsletter_unsubscribe_link' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.newsletter_unsubscribe_link',
                'newsletter_unsubscribe_url' => 'plugins/newsletter::newsletter.settings.email.templates.new_post.newsletter_unsubscribe_url',
            ],
        ],
    ],
    'variables' => [],
];
