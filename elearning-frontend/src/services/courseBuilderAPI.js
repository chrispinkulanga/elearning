import api from './api'

export const courseBuilderAPI = {
  // Show course builder interface
  async show(courseId) {
    const response = await api.get(`/courses/${courseId}/builder`)
    return response.data
  },

  // Page management
  async addPage(courseId, pageData) {
    const response = await api.post(`/courses/${courseId}/pages`, pageData)
    return response.data
  },

  async updatePage(courseId, pageId, pageData) {
    const response = await api.put(`/courses/${courseId}/pages/${pageId}`, pageData)
    return response.data
  },

  async deletePage(courseId, pageId) {
    const response = await api.delete(`/courses/${courseId}/pages/${pageId}`)
    return response.data
  },

  async duplicatePage(courseId, pageId) {
    const response = await api.post(`/courses/${courseId}/pages/${pageId}/duplicate`)
    return response.data
  },

  // Widget management
  async addWidget(pageId, widgetData) {
    const response = await api.post(`/courses/pages/${pageId}/widgets`, widgetData)
    return response.data
  },

  async updateWidget(widgetId, widgetData) {
    const response = await api.put(`/courses/widgets/${widgetId}`, widgetData)
    return response.data
  },

  async deleteWidget(widgetId) {
    const response = await api.delete(`/courses/widgets/${widgetId}`)
    return response.data
  },

  // Content reordering
  async reorderContent(courseId, reorderData) {
    const response = await api.post(`/courses/${courseId}/reorder`, reorderData)
    return response.data
  },

  // Template management
  async getTemplates(params = {}) {
    const response = await api.get('/courses/templates', { params })
    return response.data
  },

  async applyTemplate(courseId, templateId) {
    const response = await api.post(`/courses/${courseId}/apply-template`, { template_id: templateId })
    return response.data
  },

  async saveAsTemplate(courseId, templateData) {
    const response = await api.post(`/courses/${courseId}/save-as-template`, templateData)
    return response.data
  },

  // Course publishing
  async publishCourse(courseId) {
    const response = await api.post(`/instructor/courses/${courseId}/publish`)
    return response.data
  }
}
