import api from './api'

export default {
  list:          (params)           => api.get('/work-orders', { params }),
  get:           (id)               => api.get(`/work-orders/${id}`),
  create:        (data)             => api.post('/work-orders', data),
  update:        (id, data)         => api.put(`/work-orders/${id}`, data),
  remove:        (id)               => api.delete(`/work-orders/${id}`),
  updateStatus:  (id, data)         => api.patch(`/work-orders/${id}/status`, data),
  addComment:    (id, data)         => api.post(`/work-orders/${id}/comments`, data),
  addPart:       (id, data)         => api.post(`/work-orders/${id}/parts`, data),
  removePart:    (id, partId)       => api.delete(`/work-orders/${id}/parts/${partId}`),
  uploadMedia:   (id, formData)     => api.post(`/work-orders/${id}/media`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
}
