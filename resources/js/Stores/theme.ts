import { defineStore } from 'pinia';
import { usePreferredDark } from '@vueuse/core';

export const useThemeStore = defineStore('theme', {
  state: () => ({
    dark: usePreferredDark().value,
  }),
  actions: {
    boot() {
      const saved = localStorage.getItem('chatflow-theme');
      this.dark = saved ? saved === 'dark' : this.dark;
      this.sync();
    },
    toggle() {
      this.dark = !this.dark;
      localStorage.setItem('chatflow-theme', this.dark ? 'dark' : 'light');
      this.sync();
    },
    sync() {
      document.documentElement.classList.toggle('dark', this.dark);
    },
  },
});
