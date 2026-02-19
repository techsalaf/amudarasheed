<?php

namespace Botble\Newsletter\Listeners;

use Botble\Base\Events\CreatedContentEvent;
use Botble\Base\Events\UpdatedContentEvent;
use Botble\Base\Facades\EmailHandler;
use Botble\Base\Facades\Html;
use Botble\Blog\Models\Post;
use Botble\Newsletter\Enums\NewsletterStatusEnum;
use Botble\Newsletter\Models\Newsletter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SendNewsletterOnPostPublishedListener
{
    public function handle(CreatedContentEvent|UpdatedContentEvent $event): void
    {
        // Only handle post model events
        if ($event->screen !== 'form' || ! ($event->data instanceof Post)) {
            return;
        }

        /**
         * @var Post $post
         */
        $post = $event->data;

        // Only send if post is published
        if ((string) $post->status !== 'published') {
            return;
        }

        // For updates, check if status changed to published
        if ($event instanceof UpdatedContentEvent) {
            $originalStatus = $post->getOriginal('status');
            
            if ($originalStatus === 'published') {
                // Already was published, don't resend
                return;
            }
        }

        // Get post URL via slug relationship
        $postUrl = '';
        $post->loadMissing('slugable');
        
        if ($post->slugable) {
            $prefix = $post->slugable->prefix ? $post->slugable->prefix . '/' : '';
            $postUrl = url($prefix . $post->slugable->key);
        }

        // Send to all subscribed newsletter users
        Newsletter::query()
            ->where('status', NewsletterStatusEnum::SUBSCRIBED)
            ->select(['id', 'email', 'name'])
            ->chunkById(100, function ($subscribers) use ($post, $postUrl): void {
                foreach ($subscribers as $subscriber) {
                    $unsubscribeUrl = URL::signedRoute('public.newsletter.unsubscribe', ['user' => $subscriber->id]);

                    $mailer = EmailHandler::setModule(NEWSLETTER_MODULE_SCREEN_NAME)->setVariableValues([
                        'newsletter_name' => $subscriber->name ?? 'Subscriber',
                        'newsletter_email' => $subscriber->email,
                        'post_title' => $post->name,
                        'post_excerpt' => Str::limit(strip_tags((string) $post->description), 160),
                        'post_content' => $post->content,
                        'post_url' => $postUrl,
                        'post_image' => $post->image ? url($post->image) : '',
                        'newsletter_unsubscribe_link' => Html::link($unsubscribeUrl, __('here'))->toHtml(),
                        'newsletter_unsubscribe_url' => $unsubscribeUrl,
                    ]);

                    $mailer->sendUsingTemplate('new_post_notification', $subscriber->email);
                }
            });
    }
}
