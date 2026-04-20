import api from './api'

export default {
  list:         (params)   => api.get('/assets', { params }),
  get:          (id)       => api.get(`/assets/${id}`),
  create:       (data)     => api.post('/assets', data),
  update:       (id, data) => api.put(`/assets/${id}`, data),
  remove:       (id)       => api.delete(`/assets/${id}`),
  uploadMedia:  (id, formData) => api.post(`/assets/${id}/media`, formData, {
    headers: { 'Content-Type': 'multipart/form-data' },
  }),
  deleteMedia:  (mediaId)  => api.delete(`/media/${mediaId}`),
}
