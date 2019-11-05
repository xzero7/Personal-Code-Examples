import { cleanup, render } from 'react-testing-library'

import StarRating from './'

afterEach(cleanup)

describe('<StarRating />', () => {
  describe('receives a star rating and returns a Star Rating component', () => {
    const starRating = 4
    it('has Star Rating Component', () => {
      const { container } = render(<StarRating starRating={starRating} />)
      expect(
        container.querySelectorAll('svg')
      ).toHaveLength(5)
      expect(
        container.querySelectorAll('svg[color="gray"]')
      ).toHaveLength(1)
    })
    it('has meta properties set', () => {
      const { container } = render(<StarRating starRating={starRating} />)
      expect(
        container.querySelector('meta').outerHTML
      ).toBe('<meta property="reviewRating" content="4">')
    })
    it('has span tag set', () => {
      const { getByText } = render(<StarRating starRating={starRating} />)
      expect(getByText('Rating: 4 out of 5 stars')).toBeTruthy()
    })
  })
  it('receives a star rating and renders nothing if it is null', () => {
    const starRating = null
    const { getByText } = render(<StarRating starRating={starRating} />)
    expect(() => getByText(/Rating: /)).toThrow()
  })
})
