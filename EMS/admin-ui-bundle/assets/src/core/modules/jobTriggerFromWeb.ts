import type { EmsModule } from '../types/emsModule'

const SELECTOR = '[data-start-job-url]'

export default class JobTriggerFromWeb implements EmsModule {
    private readonly started = new WeakSet<HTMLElement>()

    public load(target: Document | HTMLElement): void {
        target.querySelectorAll<HTMLElement>(SELECTOR).forEach((element) => {
            if (this.started.has(element)) {
                return
            }

            this.started.add(element)

            const url = element.dataset.startJobUrl

            if (!url) {
                return
            }

            fetch(url, { method: 'POST' })
                .catch(() => undefined)
                .finally(() => location.reload())
        })
    }
}
