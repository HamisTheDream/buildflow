export function track(event: string, properties?: Record<string, any>) {
    // In development, log to console
    if (import.meta.env.DEV) {
        console.log('[Analytics]', event, properties)
    }

    // Production: Send to analytics service
    // Uncomment and configure when ready:

    // Mixpanel
    // if (window.mixpanel) {
    //   window.mixpanel.track(event, properties)
    // }

    // Google Analytics 4
    // if (window.gtag) {
    //   window.gtag('event', event, properties)
    // }

    // Amplitude
    // if (window.amplitude) {
    //   window.amplitude.track(event, properties)
    // }
}

export function identify(userId: string | number, traits?: Record<string, any>) {
    if (import.meta.env.DEV) {
        console.log('[Analytics] Identify', userId, traits)
    }

    // Mixpanel
    // if (window.mixpanel) {
    //   window.mixpanel.identify(String(userId))
    //   if (traits) window.mixpanel.people.set(traits)
    // }

    // Amplitude
    // if (window.amplitude) {
    //   window.amplitude.setUserId(String(userId))
    //   if (traits) window.amplitude.identify(new window.amplitude.Identify().set(traits))
    // }
}

export function page(name: string, properties?: Record<string, any>) {
    track('Page View', { page: name, ...properties })
}

// Common events
export const events = {
    PROJECT_CREATED: 'Project Created',
    TASK_CREATED: 'Task Created',
    ISSUE_CREATED: 'Issue Created',
    COST_ADDED: 'Cost Added',
    MEMBER_INVITED: 'Member Invited',
    SUBSCRIPTION_VIEWED: 'Subscription Viewed',
    REPORT_GENERATED: 'Report Generated',
}
