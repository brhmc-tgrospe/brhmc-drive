import { shallowMount } from '@vue/test-utils'
import { describe, it, expect, vi } from 'vitest'
import DriverConsole from '../DriverConsole.vue'
import { createTestingPinia } from '@pinia/testing'

describe('DriverConsole.vue', () => {
  it('disables the start button when isReady is false (more than 15 minutes to shift)', () => {
    const shift = {
      id: 1,
      vehicle: { unit_id: 'AMB-TEST', plate_number: 'TEST-123', vehicle_type: 'Ambulance' },
      start_time: '2026-06-08 12:00:00',
      end_time: '2026-06-08 20:00:00'
    }

    const wrapper = shallowMount(DriverConsole, {
      props: {
        shift,
        loading: false,
        unlockTime: '30m',
        isReady: false
      },
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })]
      }
    })

    // Search for the disabled button containing the text "unlocks in 30m"
    const disabledButton = wrapper.find('button[disabled]')
    expect(disabledButton.exists()).toBe(true)
    expect(disabledButton.text()).toContain('Inspection unlocks in 30m')
    expect(disabledButton.classes()).toContain('cursor-not-allowed')
  })

  it('enables the start button and changes text when isReady is true (within 15 minutes of shift)', () => {
    const shift = {
      id: 1,
      vehicle: { unit_id: 'AMB-TEST', plate_number: 'TEST-123', vehicle_type: 'Ambulance' },
      start_time: '2026-06-08 12:00:00',
      end_time: '2026-06-08 20:00:00'
    }

    const wrapper = shallowMount(DriverConsole, {
      props: {
        shift,
        loading: false,
        unlockTime: '',
        isReady: true
      },
      global: {
        plugins: [createTestingPinia({ createSpy: vi.fn })]
      }
    })

    // When isReady is true, the button becomes active and changes its text
    const activeButtons = wrapper.findAll('button').filter(btn => btn.text().includes('Accept Dispatch & Start'))
    expect(activeButtons.length).toBeGreaterThan(0)
    
    // The button should not have disabled attribute
    expect(activeButtons[0].attributes('disabled')).toBeUndefined()
  })
})
