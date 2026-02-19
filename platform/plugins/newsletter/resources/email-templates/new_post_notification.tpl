{{ header }}

<div class="bb-main-content">
    <table class="bb-box" cellpadding="0" cellspacing="0">
        <tbody>
            <tr>
                <td>
                    <table cellpadding="0" cellspacing="0">
                        <tbody>
                            @if ($post_image)
                            <tr>
                                <td class="bb-content bb-pb-0" align="center">
                                    <table class="bb-mb-lg" cellspacing="0" cellpadding="0">
                                        <tbody>
                                            <tr>
                                                <td valign="middle" align="center">
                                                    <img src="{{ $post_image }}" alt="{{ $post_title }}" style="max-width: 100%; height: auto;" class="bb-img-illustration" />
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            @endif

                            <tr>
                                <td class="bb-content bb-pb-0" align="center">
                                    <h1 class="bb-text-center bb-m-0">{{ $post_title }}</h1>

                                    @if ($post_excerpt)
                                    <p class="bb-text-center bb-mt-sm bb-mb-0 bb-text-muted">
                                        {{ $post_excerpt }}
                                    </p>
                                    @endif
                                </td>
                            </tr>

                            <tr>
                                <td class="bb-content bb-text-center">
                                    <a href="{{ $post_url }}" class="bb-btn bb-btn-primary" style="display: inline-block; padding: 12px 24px; background-color: #3490dc; color: #ffffff; text-decoration: none; border-radius: 4px; font-weight: bold;">
                                        Read Full Article
                                    </a>
                                </td>
                            </tr>

                            <tr>
                                <td class="bb-content bb-text-muted bb-text-center bb-pt-0">
                                    <p>You received this email because you subscribed to our newsletter.</p>
                                    <p>To unsubscribe, click {{ $newsletter_unsubscribe_link }}</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </td>
            </tr>
        </tbody>
    </table>
</div>

{{ footer }}
