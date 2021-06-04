import request from '@/utils/request';

/**
 * Simple RESTful resource class
 */
class ResourceSms {
  constructor(uri) {
    this.uri = uri;
  }
  list(query) {
    return request({
      url: '/' + this.uri,
      method: 'get',
      params: query,
    });
  }
  get(id, phone) {
    return request({
      url: '/' + this.uri + '/' + id + '/' + phone,
      method: 'get',
    });
  }
  getErrors(type) {
    return request({
      url: '/' + this.uri + '/' + type,
      method: 'get',
    });
  }
  getEdit(phone, user) {
    return request({
      url: '/' + this.uri + '/' + phone + '/' + user,
      method: 'get',
    });
  }
  store(resource) {
    return request({
      url: '/' + this.uri,
      method: 'post',
      data: resource,
    });
  }
  update(id, resource) {
    return request({
      url: '/' + this.uri + '/' + id,
      method: 'put',
      data: resource,
    });
  }
  destroy(id) {
    return request({
      url: '/' + this.uri + '/' + id,
      method: 'delete',
    });
  }
}

export { ResourceSms as default };
